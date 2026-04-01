<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2021-10-07
 */

namespace App\Http\Requests;

use App\Rules\CheckVehicleCostRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use App\Models\UploadData;

class UploadDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch (Route::getCurrentRoute()->getActionMethod()) {
            case 'store':
                return $this->getCustomRule();
            case 'receive-data':
            case 'receiveDataMahojin':
            case 'receiveDataJinziBugyo':
            case 'receiveDataPCA':
                return $this->getCustomRuleReceiveData();
            case 'download':
                return $this->getCustomRuleDownload();
            case 'receiveDataKyuyoBugyo':
                return $this->getCustomRuleReceiveDataZipFile();
            case 'receiveVehicleInspectionCert':
                return $this->getCustomRuleReceiveVIC();
            default:
                return [];
        }
    }

    public function getCustomRule()
    {
        if (Route::getCurrentRoute()->getActionMethod() == 'store') {
            return [
                "data_connection_id" => "required|integer",
                "file" => [
                    'bail',
                    'required',
                    'mimes:csv,xlsx,txt',
                    'mimetypes:text/csv,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'max:61440',
                    new CheckVehicleCostRule($this->get('data_connection_id'))
                ],
                "date" => ['nullable', 'date_format:Y-m-d']
            ];
        }
    }

    public function getCustomRuleReceiveData()
    {
        return [
            "file" => "required",
        ];
    }

    public function getCustomRuleReceiveDataZipFile()
    {
        return [
            "file" => "required|file|mimes:zip"
        ];
    }

    public function getCustomRuleReceiveVIC()
    {
        return [
            "file" => "required|file"
        ];
    }

    public function getCustomRuleDownload()
    {
        return [
            "item_id" => "required|exists:data_items,id",
        ];
    }

    public function messages()
    {
        return [
            'required' => trans('validation.required'),
            'integer' => trans('validation.integer'),
            'mimes' => trans('validation.mimes'),
            'exists' => trans('validation.exists'),
        ];
    }
}
