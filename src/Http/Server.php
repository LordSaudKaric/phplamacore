<?php

declare(strict_types=1);

namespace Phplama\Http;

/**
 * Description of Server
 *
 * @author LordSaudKaric
 */
class Server {

    /**
     * Server Class construct
     */
    private function __construct() {}

    public static function all(): array 
    {
        return $_SERVER;
    }
    
    public static function has(string $key): bool
    {
        return isset($_SERVER[$key]);
    }
    
    public static function get(string $key): mixed
    {
        return self::has($key) ? $_SERVER[$key] : null;
    }
    
    public static function path_info(string $paht): array
    {
        return pathinfo($paht);
    }
}
