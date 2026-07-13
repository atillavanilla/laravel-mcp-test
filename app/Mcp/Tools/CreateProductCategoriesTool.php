<?php

namespace App\Mcp\Tools;

use App\Models\ProductCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateProductCategoriesTool extends Tool
{
    public const UPSERT_CHUNK_SIZE = 50;

    protected string $description = 'Create product categories in bulk. Existing categories are matched by slug and returned without duplication.';

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'categories' => 'required|array|min:1',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.slug' => 'nullable|string|max:255',
            'categories.*.description' => 'nullable|string',
        ]);

        $categories = collect($data['categories'])
            ->map(static function (array $category): array {
                $name = trim($category['name']);

                return [
                    'name' => $name,
                    'slug' => trim($category['slug'] ?? '') ?: Str::slug($name),
                    'description' => $category['description'] ?? null,
                ];
            })
            ->filter(static fn (array $category): bool => $category['name'] !== '' && $category['slug'] !== '')
            ->unique('slug')
            ->values();

        $createdCount = 0;
        $existingCount = 0;
        $results = collect();

        foreach ($categories->chunk(self::UPSERT_CHUNK_SIZE) as $chunk) {
            foreach ($chunk as $category) {
                $model = ProductCategory::query()->firstOrCreate(
                    ['slug' => $category['slug']],
                    [
                        'name' => $category['name'],
                        'description' => $category['description'],
                    ],
                );

                $model->wasRecentlyCreated ? $createdCount++ : $existingCount++;
                $results->push($model);
            }
        }

        return Response::json([
            'message' => 'Product categories processed successfully.',
            'count' => $results->count(),
            'created_count' => $createdCount,
            'existing_count' => $existingCount,
            'categories' => $results->map(static fn (ProductCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'categories' => $schema->array()
                ->items($schema->object([
                    'name' => $schema->string()->required()->description('The category name.'),
                    'slug' => $schema->string()->nullable()->description('Optional category slug. When omitted, one is generated from the name.'),
                    'description' => $schema->string()->nullable()->description('Optional category description.'),
                ])->withoutAdditionalProperties())
                ->min(1)
                ->description('A list of category payloads to create or return if they already exist by slug. Records are processed internally in chunks of 50.'),
        ];
    }
}
