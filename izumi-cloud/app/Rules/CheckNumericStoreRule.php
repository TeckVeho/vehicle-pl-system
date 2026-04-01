<?php

namespace App\Rules;

use App\Models\Store;
use Illuminate\Contracts\Validation\Rule;

class CheckNumericStoreRule implements Rule
{
    private $param;
    private $msg;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $data = explode(":", $value);
        if($data[0] !== '' ) {
            if(!is_numeric($data[0])) {
                if($this->param === Store::FIRST_SD_TIME) {
                    $this->msg= '1便には、数字を指定してください。';
                } else {
                    $this->msg = '2便には、数字を指定してください。'; 
                }
                
                return false;
            }
           
        }
        if($data[1] !== '' ) {
            if (!is_numeric($data[1])) {
                if ($this->param === Store::FIRST_SD_TIME) {
                    $this->msg = '1便には、数字を指定してください。';
                } else {
                    $this->msg = '2便には、数字を指定してください。';
                }
                return false;
            }
        }
            
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->msg;
    }
}
