<?php

namespace App\Actions;

use App\Models\Stock;

class CreateStock
{
    public function __construct(private readonly CreateStockItems $createStockItems)
    {
    }

    public function execute(array $data): Stock
    {
        return $this->createStockItems->execute($data);
    }
}
