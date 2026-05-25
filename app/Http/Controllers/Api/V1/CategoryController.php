<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponseTrait; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $categories = Category::forUser(Auth::id())
            ->withCount('tasks')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->get();

        return $this->successResponse([
            'categories' => CategoryResource::collection($categories),
            'total'      => $categories->count(),
        ]);
    }

    private function clearDashboardCache(): void
    {
        Cache::forget('dashboard_' . Auth::id());
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);
        $this->clearDashboardCache();
        return $this->createdResponse(
            ['category' => new CategoryResource($category)],
            'Category created successfully'
        );
    }

    public function show(Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            return $this->forbiddenResponse();
        }
        $this->clearDashboardCache();
        return $this->successResponse([
            'category' => new CategoryResource($category->loadCount('tasks')),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            return $this->forbiddenResponse();
        }

        $category->update($request->validated());

        return $this->successResponse(
            ['category' => new CategoryResource($category)],
            'Category updated successfully'
        );
    }

    public function destroy(Category $category)
    {
        if ($category->user_id !== Auth::id()) {
            return $this->forbiddenResponse();
        }

        if ($category->tasks()->count() > 0) {
            return $this->errorResponse(
                'Cannot delete category with existing tasks',
                422
            );
        }

        $category->delete();
        $this->clearDashboardCache();
        return $this->deletedResponse('Category deleted successfully');
    }
}