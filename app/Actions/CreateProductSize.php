<?php

namespace App\Actions;

use App\Models\ProductSize;

class CreateProductSize
{
    public function execute(array $data): ProductSize
    {
        return ProductSize::create($data);
    }
}
