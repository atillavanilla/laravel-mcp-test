<?php

namespace App\Mcp\Tools;

use App\Models\ProductCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ShowCategoriesTool extends Tool
{
    protected string $description = "List or search product categories. Pass '*', blank, or 'all' as a wildcard to list all categories.";

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'query' => 'nullable|string|max:255',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:product_categories,id',
            'slugs' => 'nullable|array',
            'slugs.*' => 'string|max:255|exists:product_categories,slug',
            'include_product_count' => 'nullable|boolean',
        ]);

        $search = trim((string) ($data['query'] ?? ''));
        $includeProductCount = (bool) ($data['include_product_count'] ?? false);

        $query = ProductCategory::query()
            ->orderBy('name');

        if ($includeProductCount) {
            $query->withCount('products');
        }

        if ($search !== '' && $search !== '*' && strtolower($search) !== 'all') {
            $query->where(static function ($query) use ($search): void {
                $query
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (! empty($data['category_ids'])) {
            $query->whereIn('id', $data['category_ids']);
        }

        if (! empty($data['slugs'])) {
            $query->whereIn('slug', $data['slugs']);
        }

        $categories = $query->get();

        return Response::json([
            'count' => $categories->count(),
            'categories' => $categories->map(static function (ProductCategory $category) use ($includeProductCount): array {
                $payload = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                ];

                if ($includeProductCount) {
                    $payload['product_count'] = $category->products_count;
                }

                return $payload;
            })->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->nullable()->description("Optional search text for category name, slug, or description. Pass '*', blank, or 'all' to list all categories."),
            'category_ids' => $schema->array()
                ->items($schema->integer())
                ->description('Optional category ids to return.'),
            'slugs' => $schema->array()
                ->items($schema->string())
                ->description('Optional category slugs to return.'),
            'include_product_count' => $schema->boolean()->nullable()->description('Whether to include the number of products in each category.'),
        ];
    }
}
