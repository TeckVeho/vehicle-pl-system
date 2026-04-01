<?php

namespace App\Rules;

use App\Imports\ClomoImport;
use App\Imports\VehicleCostValidateImport;
use App\Models\DataConnection;
use App\Models\Employee;
use Carbon\Carbon;
use Helper\Common;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CheckVehicleCostRule implements Rule
{
    protected $msgError = null;
    protected $connection_id;
    protected $memory_limit;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($connection_id)
    {
        $this->connection_id = $connection_id;
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
        $checkConnectionCodeICL_1025 = DataConnection::query()
            ->where('id', $this->connection_id)
            ->where('data_code', 'ICL_1025')->first();
        if ($checkConnectionCodeICL_1025) {
            $import = new VehicleCostValidateImport();
            $import->import($value);

            foreach ($import->failures() as $failure) {
                $this->msgError[] = Arr::get($failure->errors(), 0);
            }
            if ($this->msgError && count($this->msgError) > 0) {
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
