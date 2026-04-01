<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StoreImageRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $image_type;
    public function __construct($image_type)
    {
        $this->image_type = $image_type;
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
        if (in_array($value, STORE_IMAGE_TYPE)) return true;
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Can not get this image';
    }
}
