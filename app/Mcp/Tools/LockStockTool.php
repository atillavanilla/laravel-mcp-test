<?php

namespace App\Mcp\Tools;

use App\Actions\LockStock;
use App\Models\Stock;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use InvalidArgumentException;

class LockStockTool extends Tool
{
    protected string $description = 'Lock a stock and sync stock item prices into the product price table.';

    public function handle(Request $request, LockStock $action): Response
    {
        $data = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
        ]);

        $stock = Stock::findOrFail($data['stock_id']);
        try {
            $locked = $action->execute($stock);
        } catch (InvalidArgumentException $exception) {
            return Response::error($exception->getMessage());
        }

        if (! $locked) {
            return Response::error('Stock could not be locked.');
        }

        return Response::json([
            'message' => 'Stock locked successfully.',
            'stock' => [
                'id' => $stock->id,
                'status' => 'locked',
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'stock_id' => $schema->integer()->description('The stock id to lock.'),
        ];
    }
}
