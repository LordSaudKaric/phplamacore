<?php
declare(strict_types=1);

namespace Phplama\Session;

class Session
{
    /**
     * Class construct
     */
    private function __construct(){}

    /**
     * Start the session
     * @return void
     */
    public static function start(): void
    {
        if (! session_id()) {
            ini_set('session.use_only_cookies', 1);
            session_start();
        }
    }
    
    /**
     * Set session value by the given key
     * @param string $key
     * @param string|int $value
     * @return string|int
     */
    public static function set(string $key, string|int $value): string|int
    {
        $_SESSION[$key] = $value;
        
        return $value;
    }
    
    /**
     * Check if the session has value by the given key
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool 
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Get the session by the given key
     * @param string $key
     * @return mixed
     */
    public static function get(string $key): mixed
    {
        return self::has($key) ? $_SESSION[$key] : null;
    }
    
    /**
     * Remove session value by the given key
     * @param string $key
     * @return void
     */
    public static function remove(string $key): void 
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * All session values
     * @return array
     */
    public static function all(): array 
    {
        return $_SESSION;        
    }
    
    /**
     * Destroy all session values
     * @return void
     */
    public static function destry(): void
    {
        foreach (self::all() as $key => $value) {
            self::remove($key);
        }
    }
    
    /**
     * Flash the session value by the given key
     * @param string $key
     * @return mixed
     */
    public static function flash(string $key): mixed
    {
        $value = null;
        
        if (self::has($key)) {
            $value = self::get($key);
            self::remove($key);
        }
        
        return $value;
    }
    
}