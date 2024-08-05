<?php

declare(strict_types=1);


namespace Phplama\Validation\Rules;

use App\Models\DbModel;
use Mazed\PHPValidator\Rule;

/**
 * Description of UniqueRule
 *
 * @author LordSaudKaric
 */
class UniqueRule extends Rule
{
    // error message if fails...
    private $message = "The :attribute must be a boolean";

    public function validate(string $table, string $column, $value)
    {
        // validation code here...
        $data = DbModel::table($table)->where($column, $value)->fetch();
        return $data ? false : true;
    }

    public function message()
    {
        return $this->message;
    }
}
