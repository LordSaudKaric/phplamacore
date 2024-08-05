<?php

declare(strict_types=1);

namespace Phplama\Cookie;

/**
 * Description of Cookie
 *
 * @author LordSaudKaric
 */
class Cookie {

    /**
     * Cookie Class construct
     */
    private function __construct() {
        
    }
    
    /**
     * Set Cookie value by the given key
     * @param string $key
     * @param string|int $value
     * @return string|int
     */
    public static function set(string $key, string|int $value): string|int
    {
        setcookie($key, $value, strtotime('+5 days'), '/', '', false, true);
        
        return $value;
    }
    
    /**
     * Check if the Cookie has value by the given key
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool 
    {
        return isset($_COOKIE[$key]);
    }
    
    /**
     * Get the Cookie by the given key
     * @param string $key
     * @return mixed
     */
    public static function get(string $key): mixed
    {
        return self::has($key) ? $_COOKIE[$key] : null;
    }
    
    /**
     * Remove Cookie value by the given key
     * @param string $key
     * @return void
     */
    public static function remove(string $key): void 
    {
        setcookie($key, '', -1, '/', '', false, true);
    }
    
    /**
     * All Cookie values
     * @return array
     */
    public static function all(): array 
    {
        return $_COOKIE;        
    }
    
    /**
     * Destroy all Cookie values
     * @return void
     */
    public static function destry(): void
    {
        foreach (self::all() as $key => $value) {
            self::remove($key);
        }
    }
}
