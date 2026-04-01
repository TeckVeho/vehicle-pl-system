<?php
/**
 * Created by VeHo.
 * Year: 2023-03-22
 */

namespace Repository;

use App\Models\InsuranceRate;
use App\Repositories\Contracts\InsuranceRateRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use App\Models\InsuranceRateHistory;
use Illuminate\Support\Facades\DB;

class InsuranceRateRepository extends BaseRepository implements InsuranceRateRepositoryInterface
{

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
       * Instantiate model
       *
       * @param InsuranceRate $model
       */

    public function model()
    {
        return InsuranceRate::class;
    }

    public function updateInsuranceRate($attributes, $id) 
    {
        $insuranceRate = InsuranceRate::find($id);
        $insuranceRate->change_rate =  $attributes['change_rate'];
        $insuranceRate->applicable_date =  $attributes['applicable_date'];
        $insuranceRate->save();
        return $insuranceRate;
    }

    public function listHistory()
    {
        $rate_history = DB::table('insurance_rate_history')
            ->leftJoin('insurance_rates', 'insurance_rates.id', '=', 'insurance_rate_history.insurance_rates_id')
            ->select('insurance_rate_history.*', 'insurance_rates.kinds', 'insurance_rates.name')
            ->orderBy('insurance_rate_history.applicable_date', 'DESC')
            ->get();
        
        return $rate_history;
    }

}
