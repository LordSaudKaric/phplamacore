<?php

declare(strict_types=1);

namespace Phplama\Router;

class Route 
{
    private static array    $routes      = [];
    private static string   $middlewere  = '';
    private static string   $prefix      = '';
    private static string   $not_found   = 'ErrorController';

    /**
     * Route Class construct
     */
    private function __construct() {}

    private static function add(string $method, string $uri, $callback): void
    {
        $uri = rtrim(self::$prefix . '/' . trim($uri, '/'), '/');
        $uri = $uri?:'/';
        
        self::$routes[] = [
            'uri' => $uri,
            'callback' => $callback,
            'method' => $method,
            'middlewere' => self::$middlewere
        ];
    }
    
    public static function get(string $uri, $callback): void
    {
        self::add('GET', $uri, $callback);
    }
    
    public static function post(string $uri, $callback): void
    {
        self::add('POST', $uri, $callback);
    }
    
    public static function put(string $uri, $callback): void
    {
        self::add('PUT', $uri, $callback);
    }
    
    public static function patch(string $uri, $callback): void
    {
        self::add('PATCH', $uri, $callback);
    }
    
    public static function delete(string $uri, $callback): void
    {
        self::add('DELETE', $uri, $callback);
    }
    
    public static function prefix(string $prefix, $callback) 
    {
        $parent_prefix = self::$prefix;
        self::$prefix .= '/' . trim($prefix, '/');
        
        if (is_callable($callback)) {
            call_user_func($callback);
        } else {
            throw new InvalidCallbackArgumentException('Please provide valid callback function');
        }
        
        self::$prefix = $parent_prefix;
    }
    
    public static function middlewere(string $middlewere, $callback) 
    {
        $parent_middlewere = self::$middlewere;
        self::$middlewere .= '|' . trim($middlewere, '|');
        
        if (is_callable($callback)) {
            call_user_func($callback);
        } else {
            throw new InvalidCallbackArgumentException('Please provide valid callback function');
        }
        
        self::$middlewere = $parent_middlewere;
    }
    
    public static function handle(string $uri, string $method): mixed
    {        
        foreach (self::$routes as $route) 
        {
            $matched = true;
            $route['uri'] = preg_replace('/\/{(.*?)}/', '/(.*?)', $route['uri']);
            $route['uri'] = '#^' .  $route['uri'] . '$#';
            
            if (preg_match($route['uri'], $uri, $matches)) {
                array_shift($matches);                
                $params = array_values($matches);
                
                foreach ($params as $param) 
                {
                    if (strpos($param, '/')) $matched = false;
                }
                
                if ($route['method'] != $method) $matched = false;
                
                if ($matched == true) {
                    return self::invoke($route, $params);
                }
            }
        }
        
        return self::invokeNotFound();
    }
    
    public static function invokeNotFound(): mixed
    {
        $controller = 'App\\Controllers\\' . self::$not_found;
        
        $object = new $controller();
        
        return call_user_func_array([$object, 'handle'], []);
    }
    
    public static function notFound(string $not_found): void
    {
        self::$not_found = $not_found;
    }

    public static function invoke(array $route, array $params): mixed
    {
        self::executeMiddlewere($route);
        
        $callback = $route['callback'];
                
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }
                
        if (is_array($callback)) {
            $controller = $callback[0];
            $method = $callback[1] . 'Action';
        }
        
        if (is_string($callback) && strpos($callback, '@') !== false) {
            list($controller, $method) = explode('@', $callback);

            $controller = 'App\\Controllers\\' . $controller;
            $method = $method . 'Action';
        }

        if (! class_exists($controller)) {
            throw new \Exception(
                sprintf('The: %s doesnot exists, please provide valid one!', $controller)
            );
        }

        $object = new $controller();

        if (! method_exists($object, $method)) {
            throw new \Exception(
                sprintf('The method: %s is not exiest at %s !', $method, $controller)
            );
        }
        
        return call_user_func_array([$object, $method], $params);
    }

    public static function executeMiddlewere(array $route)
    {
        $middleweres = explode('|', $route['middlewere']);
        
        foreach ($middleweres as $middlewere) 
        {
            if ($middlewere != '') 
            {
                $middlewere = 'App\\Middleweres\\' . ucfirst($middlewere) . 'Middlewere';
                
                if (! class_exists($middlewere)) {
                    throw new \Exception(
                        sprintf('Class %s does not exists!', $middlewere)
                    );
                }
                
                $object = new $middlewere();
                
                if (!method_exists($object, 'handle')) {
                    throw new \Exception(
                        sprintf('Method "handle" does not exists on the  Class %s!', $middlewere)
                    );
                }
                
                call_user_func_array([$object, 'handle'], []);
            }
        }
    }
}