<?php

namespace App\Modules\Category\Controller;

use App\Http\Controllers\Controller;
use App\Models\Category as CategoryModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Category extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => ['sometimes', 'nullable', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $categories = CategoryModel::query()
            ->where('is_active', true)
            ->when(array_key_exists('parent_id', $validated), function (Builder $query) use ($validated) {
                $validated['parent_id'] === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $validated['parent_id']);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($validated['per_page'] ?? 20);

        $categories->through(fn (CategoryModel $category) => $this->categoryData($category));

        return response()->json($categories);
    }

    public function show(string $slug): JsonResponse
    {
        $category = CategoryModel::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->with(['children' => fn (Builder $query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->orderBy('id')])
            ->firstOrFail();

        return response()->json([
            'data' => $this->categoryData($category),
        ]);
    }

    /** @return array<string, mixed> */
    private function categoryData(CategoryModel $category): array
    {
        $data = [
            'id' => $category->id,
            'parent_id' => $category->parent_id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
        ];

        if ($category->relationLoaded('children')) {
            $data['children'] = $category->children
                ->map(fn (CategoryModel $child) => $this->categoryData($child))
                ->values()
                ->all();
        }

        return $data;
    }
}
