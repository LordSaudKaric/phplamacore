<?php

declare(strict_types=1);

namespace Phplama\Validation;

use Mazed\PHPValidator\Validator;
use Phplama\Session\Session;
use Phplama\Url\Url;
use Phplama\Validation\Rules\UniqueRule;

/**
 * Description of Validate
 *
 * @author LordSaudKaric
 */
class Validate 
{

    /**
     * Validate Class construct
     */
    private function __construct() {}

    //put your code here
    
    public static function validate(
            array $data,
            array $rules,
            array $attributes = [],
            array $customMessages = [],
            bool $json = false): mixed
    {
        $validator = new Validator;
        
        $validation = $validator->make($data, $rules, [$customMessages, $attributes]);
        
        $errors = $validation->errors();
        
        if ($validation->fails()) {
            
            if ($json == true) {
                return ['errors' => $errors];
            } else {
                Session::set('errors', $errors);
                Session::set('old', $data);
                
                return Url::redirect(Url::previous());
            }
        }
    }
}
