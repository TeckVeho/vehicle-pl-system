<?php

namespace App\Rules;

use App\Models\Store;
use Illuminate\Contracts\Validation\Rule;

class StorePassCodeRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    const LENGTH = 4;
    private $store;
    private $message;
    public function __construct(Store $store)
    {
        $this->store = $store;
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
        if ($this->store->pass_code == null) {
            return true;
        }

        if ($this->store->pass_code != null) {
            if ($value == $this->store->pass_code) {
                return true;
            } else {
                $this->message = 'パスコードが一致しません';
                return false;
            }
        }

        $this->message = '無効なパスコードです';
        return false;
    }

    public function is_required()
    {
        if ($this->store->pass_code !== null) return "required";
        return "nullable";
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
