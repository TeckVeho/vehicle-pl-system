<?php

namespace Repository;

use App\Models\QuotationMasterData;
use App\Repositories\Contracts\QuotationMasterDataRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;

class QuotationMasterDataRepository extends BaseRepository implements QuotationMasterDataRepositoryInterface
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function model()
    {
        return QuotationMasterData::class;
    }

    public function updateAll($request)
    {
        $data = $request->all();
        
        foreach ($data as $key => $item) {
            $quotationMasterData = $this->model->where('tonnage', $key)->first();
            if ($quotationMasterData) {
                $quotationMasterData->update($item);
            } else {
                $this->model->create($item);
            }
        }
        $quotationMasterDatas = $this->model->get();
        return $quotationMasterDatas->map(function ($item) {
            return [
                "{$item->tonnage}" => [
                "carInspectionPrice" => $item->car_inspection_price,
                "regularInspectionPrice" => $item->regular_inspection_price,
                "tirePrice" => $item->tire_price,
                "oilChangePrice" => $item->oil_change_price,
                "fuelUnitPrice" => $item->fuel_unit_price,
                ]
            ];
        });
    }

    public function findByTonnage($tonnage)
    {
        return $this->model->where('tonnage', $tonnage)->first();
    }

    public function getAllGroupedByTonnage()
    {
        $data = $this->model->all();
        $grouped = [];

        foreach ($data as $item) {
            $grouped[$item->tonnage] = [
                'car_inspection_price' => $item->car_inspection_price,
                'regular_inspection_price' => $item->regular_inspection_price,
                'tire_price' => $item->tire_price,
                'oil_change_price' => $item->oil_change_price,
                'fuel_unit_price' => $item->fuel_unit_price,
            ];
        }

        return $grouped;
    }
}
