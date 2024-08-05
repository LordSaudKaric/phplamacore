<?php

declare(strict_types=1);

namespace Phplama\Url;

use Phplama\Http\Request;
/**
 * Description of Url
 *
 * @author LordSaudKaric
 */
class Url 
{

    /**
     * Url Class construct
     */
    private function __construct() {}
    
    public static function path(string $path): string
    {
        return Request::baseUrl() . '/' . trim($path, '/');
    }
    
    public static function previous(): string
    {
        return Request::previous();
    }
    
    public static function redirect(string $path)
    {
        header('Location: ' . $path);
        exit();
    }
    
}
