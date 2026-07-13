<?php

namespace App\Actions;

use App\Models\ProductCategory;

class CreateProductCategory
{
    public function execute(array $data): ProductCategory
    {
        return ProductCategory::create($data);
    }
}
