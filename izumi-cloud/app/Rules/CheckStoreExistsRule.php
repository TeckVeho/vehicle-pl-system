<?php

namespace App\Rules;

use App\Models\Route;
use App\Models\Store;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class CheckStoreExistsRule implements Rule
{
    protected $msgError;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value) {
            $listVals = explode(',', $value);
            $lstkey = [];
            foreach ($listVals as $key => $listVal) {
                $chkStore = Store::where('store_name', Str::of($listVal)->trim())->first();
                if (!$chkStore) {
                    $lstkey[] = Str::of($listVal)->trim();
                }
            }
            $this->msgError = 'インポートファイルの{:index}行目にエラーがあります。';
            if (count($lstkey) > 0) {
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
        return $this->msgError;
    }
}
