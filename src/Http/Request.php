<?php

declare(strict_types=1);

namespace Phplama\Http;

/**
 * Description of Request
 *
 * @author LordSaudKaric
 */
class Request 
{
    private static $base_url;
    private static $url;
    private static $full_url;
    private static $query_string;
    private static $script_name;
    
    /**
     * Request Class construct
     */
    private function __construct() {}

    /**
     * Handle the request
     * @return void
     */
    public static function handle(): void
    {
        self::$script_name = str_replace('\\', '', dirname(Server::get('SCRIPT_NAME')));
        self::setBaseUrl();
        self::setUrl();
    }
    
    /**
     * Set BaseUrl
     * @return void
     */
    private static function setBaseUrl(): void
    {
        $protocl = Server::get('REQUEST_SCHEME') . '://';
        $host    = Server::get('HTTP_HOST');
        $script_name = self::$script_name;
        
        self::$base_url = $protocl . $host . $script_name;
    }

    /**
     * Set Url
     * @return void
     */
    private static function setUrl(): void
    {
        $request_uri = urldecode(Server::get('REQUEST_URI'));
        $request_uri = preg_replace("#^". self::$script_name . "#", "", $request_uri);
        $request_uri = rtrim($request_uri, '/');
        
        self::$full_url     = $request_uri;
        
        self::$url          = parse_url($request_uri)['path'] ?: '/';
        self::$query_string = parse_url($request_uri)['query'];

    }

    /**
     * Get Base Url
     * @return string
     */
    public static function baseUrl(): string
    {
        return self::$base_url;
    }
    
    /**
     * Get url
     * @return string
     */
    public static function url(): string
    {
        return self::$url;
    }
    
    /**
     * Get query_string
     * @return string
     */
    public static function query_string(): string
    {
        return self::$query_string;
    }
    
    /**
     * Get Full url
     * @return string
     */
    public static function fullUrl(): string
    {
        return self::$full_url;
    }
    
    /**
     * Get Request Method
     * @return string
     */
    public static function method(): string
    {
        return Server::get('REQUEST_METHOD');        
    }
    
    /**
     * Check if request has a key
     * @param array $type
     * @param string $key
     * @return bool
     */
    public static function has(array $type, string $key): bool 
    {
        return array_key_exists($key, $type);
    }
    
    /**
     * Return value from request by the key
     * @param string $key
     * @param array $type
     * @return mixed
     */
    public static function value(string $key, array $type = null): mixed 
    {
        $type = isset($type) ? $type : $_REQUEST;
        
        return self::has($type, $key) ? $type[$key] : null;
    }

    /**
     * Return key from GET request
     * @param string $key
     * @return mixed
     */
    public static function get(string $key): mixed
    {
        return self::value($key, $_GET);
    }
    
    /**
     * Return key from POST request
     * @param string $key
     * @return mixed
     */
    public static function post(string $key): mixed
    {
        return self::value($key, $_POST);
    }
    
    /**
     * Set key in request
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function set(string $key, mixed $value): mixed
    {
        $_GET[$key]     = $value;
        $_POST[$key]    = $value;
        $_REQUEST[$key] = $value;
        
        return $value;
    }
    
    /**
     * Get previous link
     * @return string
     */
    public static function previous(): string
    {
        return Server::get('HTTP_REFERER');
    }
    
    /**
     * Get All request data
     * @return array
     */
    public static function all(): array
    {
        return $_REQUEST;
    }
}