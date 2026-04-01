<?php
/**
 * Created by PhpStorm.
 * User: autoDump
 * Year: 2022-11-10
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class DriverRecorderRequest extends FormRequest
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
            case 'update':
                return $this->getCustomRule();
            case 'store':
                return $this->getCustomRule();
            case 'addOrUpdatePlayList':
                return $this->getCustomRule();
            default:
                return [];
        }
    }

    public function getCustomRule()
    {
        if (Route::getCurrentRoute()->getActionMethod() == 'update') {
            return [
                'id' => 'required|exists:driver_recorders,id',
                "record_date" => "required|date_format:Y-m-d",
                "department_id" => 'required|exists:departments,id',
                // "type" => 'required|in:' . implode(',', DRIVER_RECORDER_TYPE),
                "title" => "required|string|max:255",
//                "list_recorder" => "required|array|max:10",
//                "list_recorder.*" => "required",
//                "list_recorder.*.movie_title" => "required|string|max:255",
//                "list_recorder.*.list_movie" => "required|min:1",
//                "list_recorder.*.list_movie.front" => "required_without_all:list_recorder.*.list_movie.inside,list_recorder.*.list_movie.behind|exists:files,id",
//                "list_recorder.*.list_movie.inside" => "required_without_all:list_recorder.*.list_movie.front,list_recorder.*.list_movie.behind|exists:files,id",
//                "list_recorder.*.list_movie.behind" => "required_without_all:list_recorder.*.list_movie.front,list_recorder.*.list_movie.inside|exists:files,id",
                "remark" => "nullable|string|max:1000",
                "type_one" => 'required|in:' . implode(',', TYPE_ONE),
                "type_two" => 'required|in:' . implode(',', TYPE_TOW),
                "shipper" => 'required|in:' . implode(',', SHIPPER),
                "accident_classification" => 'required|in:' . implode(',', ACCIDENT_CLASSIFICATION),
                "place_of_occurrence" => 'required|in:' . implode(',', PLACE_OF_OCCURRENCE)
            ];
        }
        if (Route::getCurrentRoute()->getActionMethod() == 'store') {
            return [
                "record_date" => "required|date_format:Y-m-d",
                "department_id" => 'required|exists:departments,id',
                // "type" => 'required|in:' . implode(',', DRIVER_RECORDER_TYPE),
                "title" => "required|string|max:255",
                "list_recorder" => "required|array|max:10",
//                "list_recorder.*" => "required",
//                "list_recorder.*.movie_title" => "required|string|max:255",
//                "list_recorder.*.list_movie" => "required|min:1",
//                "list_recorder.*.list_movie.front" => "required_without_all:list_recorder.*.list_movie.inside,list_recorder.*.list_movie.behind|exists:files,id",
//                "list_recorder.*.list_movie.inside" => "required_without_all:list_recorder.*.list_movie.front,list_recorder.*.list_movie.behind|exists:files,id",
//                "list_recorder.*.list_movie.behind" => "required_without_all:list_recorder.*.list_movie.front,list_recorder.*.list_movie.inside|exists:files,id",
                "remark" => "nullable|string|max:1000",
                "type_one" => 'required|in:'. implode(',', TYPE_ONE),
                "type_two" => 'required|in:'. implode(',', TYPE_TOW),
                "shipper" => 'required|in:'. implode(',', SHIPPER),
                "accident_classification" => 'required|in:'. implode(',', ACCIDENT_CLASSIFICATION),
                "place_of_occurrence" => 'required|in:'. implode(',', PLACE_OF_OCCURRENCE)
            ];
        }
        if (Route::getCurrentRoute()->getActionMethod() == 'addOrUpdatePlayList') {
            return [
                "list_play_list" => "nullable|array",
                "list_play_list.*" => "nullable|exists:driver_play_lists,id",
            ];
        }
    }

    protected function prepareForValidation()
    {
        if (Route::getCurrentRoute()->getActionMethod() == 'update') {
            $this->merge(['id' => $this->route('driver_recorder')]);
        }
    }

    public function messages()
    {
        return [
//            "list_recorder.required" => "最低1つでも動画がアップロードされている",
//            "list_recorder.*.required" => "最低1つでも動画がアップロードされている",
//            "list_recorder.*.list_movie.required" => "最低1つでも動画がアップロードされている",
            "type_one.required" => "事故GPは必須です",
            "type_two.required" => "有責無責は必須です",
            "shipper.required" => "荷主は必須です",
            "accident_classification.required" => "事故区分は必須です",
            "place_of_occurrence.required" => "発生場所は必須です",
        ];
    }

    public function attributes()
    {
        return [
            "record_date" => "発生日時",
            'department_id' => '拠点',
            "title " => "タイトル",
            "type" => '種別',
//            "list_recorder.*.list_movie.front" => 'ファイル(前方)',
//            "list_recorder.*.list_movie.inside" => 'ファイル(車内)',
//            "list_recorder.*.list_movie.behind" => 'ファイル(後方)',
            "type_one" => "事故GP",
            "type_two" => "有責無責",
            "shipper" => "荷主",
            "accident_classification" => "事故区分",
            "place_of_occurrence" => "発生場所",
        ];
    }
}
