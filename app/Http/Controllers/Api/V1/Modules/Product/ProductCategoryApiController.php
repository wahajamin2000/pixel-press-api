<?php

namespace App\Http\Controllers\Api\V1\Modules\Product;

use App\Enums\StatusEnum;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Http\Resources\Lookups\CategoryLookupResource;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Models\User;
use App\Traits\HasMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class ProductCategoryApiController extends Controller
{
    use HasMedia;

    /**
     * Display a listing of products
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductCategory::with('products');

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Only active product categories for public API
        if (!auth()->user()->level === User::LEVEL_SUPER_ADMIN) {
            $query->active();
        }

        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy('name', $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $product_categories = $query->paginate($perPage);

        return $this->successResponse('Fetched Successfully!', [
            'product_categories' => CategoryLookupResource::collection($product_categories),
            'pagination' => [
                'current_page' => $product_categories->currentPage(),
                'last_page' => $product_categories->lastPage(),
                'per_page' => $product_categories->perPage(),
                'total' => $product_categories->total(),
                'from' => $product_categories->firstItem(),
                'to' => $product_categories->lastItem(),
            ],
        ]);
    }

    public function getActiveProds(Request $request): JsonResponse
    {
        $query = ProductCategory::with('products')->active();

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Sorting
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy('name', $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $product_categories = $query->paginate($perPage);

        return $this->successResponse('Fetched Successfully!', [
            'product_categories' => CategoryLookupResource::collection($product_categories),
            'pagination' => [
                'current_page' => $product_categories->currentPage(),
                'last_page' => $product_categories->lastPage(),
                'per_page' => $product_categories->perPage(),
                'total' => $product_categories->total(),
                'from' => $product_categories->firstItem(),
                'to' => $product_categories->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $product_category = ProductCategory::create($request->validated());

        $product_category->created_by = Auth::id();
        $product_category->save();

        $product_category->load('products');

        return $this->successResponse('ProductCategory created successfully', [
                'product_category' => new ProductCategoryResource($product_category)
            ]
        );
    }

    /**
     * Display the specified product
     */
    public function show(ProductCategory $product_category): JsonResponse
    {
        $product_category->load('products');

        return $this->successResponse('ProductCategory retrieved successfully',[
            'product_category' => new ProductCategoryResource($product_category)
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(UpdateProductCategoryRequest $request, ProductCategory $product_category): JsonResponse
    {
        $product_category->update($request->validated());

        $product_category->updated_by = Auth::id();
        $product_category->save();

        return $this->successResponse('ProductCategory updated successfully',[
            'product_category' => new ProductCategoryResource($product_category)
        ]);
    }


    /**
     * Update the specified product status
     */
    public function updateStatus(Request $request, ProductCategory $product_category): JsonResponse
    {
        $request->validate([
            'status' => ['required', new Enum(StatusEnum::class)],
        ]);

        $product_category->status = $request->status ?? $product_category->status;
        $product_category->updated_by = Auth::id();
        $product_category->save();

        return $this->successResponse('ProductCategory status updated successfully',[]);
    }

    /**
     * Remove the specified product
     */
    public function destroy(ProductCategory $product_category): JsonResponse
    {
        $product_category->delete();

        return $this->successResponse('ProductCategory deleted successfully', []);
    }

    /**
     * Search products
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'per_page' => 'integer|min:1|max:50',
        ]);

        $product_categories = ProductCategory::active()
            ->search($request->q)
            ->orderBy('name')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse('Search results retrieved successfully', [
            'product_categories' => ProductCategoryResource::collection($product_categories),
            'pagination' => [
                'current_page' => $product_categories->currentPage(),
                'last_page' => $product_categories->lastPage(),
                'per_page' => $product_categories->perPage(),
                'total' => $product_categories->total(),
                'from' => $product_categories->firstItem(),
                'to' => $product_categories->lastItem(),
            ],
        ]);
    }
}
