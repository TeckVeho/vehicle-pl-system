<?php

namespace Repository;

use App\Models\Quotation;
use App\Repositories\Contracts\QuotationRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;

class QuotationRepository extends BaseRepository implements QuotationRepositoryInterface
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function model()
    {
        return Quotation::class;
    }

    public function search($params)
    {
        $tonnage_id = Arr::get($params, 'tonnage_id');
        $search = Arr::get($params, 'search');
        $sortBy = Arr::get($params, 'sortBy', 'created_at');
        $sortOrder = Arr::get($params, 'sortOrder', 'desc');
        $query = $this->model->newQuery();
        $query->with(['author', 'quotationMasterData', 'deliveryLocations']);
        if ($tonnage_id) {
            $query->whereHas('quotationMasterData', function ($q) use ($tonnage_id) {
                $q->where('id', $tonnage_id);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('author', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($sortBy) {
            $query->orderBy($sortBy, $sortOrder);
        }
        return $query;
    }

    public function searchWithPagination($params)
    {
        $perPage = Arr::get($params, 'per_page', 10);
        $count = $this->model->newQuery()->count();
        $query = $this->search($params);
        $query->with(['author', 'quotationMasterData', 'deliveryLocations']);
        $data = $query->paginate($perPage);
        return [
            'result' => $data,
            'total_all' => $count,
        ];
    }

    public function filterByTonnage($tonnage)
    {
        if (is_numeric($tonnage)) {
            return $this->model->where('tonnage', $tonnage);
        } else {
            return $this->model->whereHas('quotationMasterData', function ($q) use ($tonnage) {
                $q->where('tonnage', $tonnage);
            });
        }
    }

    public function sortBy($sortBy, $sortOrder = 'desc')
    {
        return $this->model->orderBy($sortBy, $sortOrder);
    }

    public function create(array $attributes)
    {
        return \DB::transaction(function () use ($attributes) {
            $deliveryLocations = $attributes['delivery_locations'] ?? [];
            unset($attributes['delivery_locations']);
            
            $quotation = $this->model->create($attributes);
            
            // Fix bug: Địa chỉ bị mất - thêm validation và trim
            if (!empty($deliveryLocations) && is_array($deliveryLocations)) {
                foreach ($deliveryLocations as $index => $location) {
                    // Trim và kiểm tra location không rỗng
                    $trimmedLocation = is_string($location) ? trim($location) : '';
                    if (!empty($trimmedLocation)) {
                        $quotation->deliveryLocations()->create([
                            'location_name' => $trimmedLocation,
                            'sequence_order' => $index + 1,
                        ]);
                    }
                }
            }
            
            return $quotation->load('deliveryLocations', 'author', 'quotationMasterData');
        });
    }

    public function update(array $attributes, $id)
    {
        return \DB::transaction(function () use ($attributes, $id) {
            $quotation = $this->model->findOrFail($id);
            
            $deliveryLocations = $attributes['delivery_locations'] ?? null;
            unset($attributes['delivery_locations']);
            
            $quotation->update($attributes);
            
            // Fix bug: Địa chỉ bị mất - cải thiện logic xử lý delivery_locations
            if ($deliveryLocations !== null) {
                // Xóa tất cả delivery locations cũ
                $quotation->deliveryLocations()->delete();
                
                // Tạo lại delivery locations mới nếu có
                if (!empty($deliveryLocations) && is_array($deliveryLocations)) {
                    foreach ($deliveryLocations as $index => $location) {
                        // Trim và kiểm tra location không rỗng
                        $trimmedLocation = is_string($location) ? trim($location) : '';
                        if (!empty($trimmedLocation)) {
                            $quotation->deliveryLocations()->create([
                                'location_name' => $trimmedLocation,
                                'sequence_order' => $index + 1,
                            ]);
                        }
                    }
                }
            }
            
            return $quotation->load('deliveryLocations', 'author', 'quotationMasterData');
        });
    }
}