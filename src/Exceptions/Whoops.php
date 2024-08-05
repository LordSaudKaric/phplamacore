<?php
declare(strict_types=1);

namespace Phplama\Exceptions;

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Whoops
{
    private function __construct(){}

    public static function handle(
        string $env = 'production',
        bool $debug = false): void
    {
        // Turn off all error reporting
        ini_set("display_errors", 1);
        error_reporting(1);
                
        $whoops = new Run;
        
        switch ([$debug, $env]) 
        {
            case [true, 'local']:
            case [true, 'test']:
            case [true, 'development']:    
                $whoops->pushHandler(new PrettyPageHandler);
                break;
            case [true, 'production']:
                $whoops->allowQuit(false);
                $whoops->writeToOutput(false);
                break;
        }
        
        $whoops->register();
    }
}