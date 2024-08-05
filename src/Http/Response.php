<?php
declare(strict_types=1);

namespace Phplama\Http;

/**
 * Description of Response
 *
 * @author LordSaudKaric
 */
class Response {

    /**
     * Response Class construct
     */
    private function __construct() {}
    
    public static function json(mixed $data): mixed
    {
        return json_encode($data);
    }

    public static function output(mixed $data)
    {
        if ( ! $data) return;
        
        if ( !is_string($data)) {
            $data = self::json($data);
        }
        
        echo $data;
    }
}
