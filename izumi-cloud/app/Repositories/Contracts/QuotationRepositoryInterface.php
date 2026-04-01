<?php

namespace App\Repositories\Contracts;

interface QuotationRepositoryInterface extends BaseRepositoryInterface
{
    public function search($params);
    public function searchWithPagination($params);
    public function filterByTonnage($tonnage);
    public function sortBy($sortBy, $sortOrder = 'desc');
}
