<?php

declare(strict_types=1);

namespace Phplama\File;
/**
 * Description of File
 *
 * @author LordSaudKaric
 */
class File 
{

    /**
     * File Class construct
     */
    private function __construct() {}
    
    
    /**
     * Get path to the root folder
     * @return string
     */
    public static function root(): string
    {
        return ROOT_DIR;
    }
    
    /**
     * Get directory separator
     * @return string
     */
    public static function ds(): string
    {
        return DS;
    }
    
    /**
     * define path to the file
     * @param string $path
     * @return string
     */
    public static function path(string $path): string
    {
        $path = self::root() . self::ds() . trim($path, '/');
        $path = str_replace(['/', '\\'], self::ds(), $path);
        
        return $path;
    }
    
    /**
     * Check that the file exists
     * @param string $path
     * @return bool
     */
    public static function exist(string $path): bool
    {
        return file_exists(self::path($path));
    }
    
    /**
     * Require file
     * @param string $path
     * @return type
     */
    public static function require_file(string $path)
    {
        if (self::exist($path)) {
            return require_once self::path($path);
        }
    }
    
    /**
     * Include file
     * @param string $path
     * @return type
     */
    public static function include_file(string $path)
    {
        if (self::exist($path)) {
            return include_once self::path($path);
        }
    }
    
    /**
     * Require directory
     * @param string $path
     */
    public static function require_directory(string $path)
    {
        $files = array_diff(scandir(self::path($path)), ['.', '..']);
        
        foreach ($files as $file) 
        {
            // In case ther is an error log file i that directory
            if ($file == 'error_log') continue;
            
            $file_path = $path . self::ds() . $file;
            
            if (self::exist($file_path)) {
                self::require_file($file_path);
            }
        }
    }
}
