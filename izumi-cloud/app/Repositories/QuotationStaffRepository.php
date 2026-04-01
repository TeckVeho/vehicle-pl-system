<?php

namespace Repository;

use App\Models\QuotationStaff;
use App\Repositories\Contracts\QuotationStaffRepositoryInterface;
use Repository\BaseRepository;
use Illuminate\Foundation\Application;

class QuotationStaffRepository extends BaseRepository implements QuotationStaffRepositoryInterface
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function model()
    {
        return QuotationStaff::class;
    }
}
