<?php

declare(strict_types=1);

namespace Phplama\View;

use Exception;
use Jenssegers\Blade\Blade;
use Phplama\File\File;
use Phplama\Session\Session;

/**
 * Description of View
 *
 * @author LordSaudKaric
 */
class View 
{

    /**
     * View Class construct
     */
    private function __construct() {}
    
    //put your code here
    
    public static function render(string $view, array $vars = [], string $type = null): string
    {
        $errors = Session::flash('errors');
        $old = Session::flash('old');
        
        $vars = array_merge($vars, ['errors' => $errors, 'old' => $old]);
        
        $render = $type ? $type . 'Render' : 'bladeRender';
        return self::$render($view, $vars);
    }
    
    public static function bladeRender(string $view, array $vars = []): string
    {
        $blade = new Blade(File::path('views'), File::path('storage/cache'));
        return $blade->make($view, $vars)->render();        
    }
    
    
    public static function viewRender(string $view, array $vars = []): string
    {
        $path = 'views' . File::ds() . str_replace(['/', '\\', '.', '|', '@', '#'], File::ds(), $view) . '.php';
        
        if (! File::exist($path)) {
            throw new Exception(
                sprintf('The view file: %s toes not exists', $path)
            );
        }
        
        ob_start();
        extract($vars);
        include File::path($path);
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }
}
