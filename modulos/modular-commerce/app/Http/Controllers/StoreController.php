<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Product;
use App\Modules\Vendor\Requests\CreateProductRequest;
use App\Modules\Vendor\Requests\UpdateProductRequest;
use App\Modules\Vendor\Services\ProductService;
use App\Modules\Vendor\Vendor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Show public store page.
     */
    public function show(string $slug): Response
    {
        $vendor = Vendor::where('store_slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $products = $vendor->products()
            ->where('status', 'active')
            ->with('variants')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($product) {
                $product->featured_image_url = $product->getFeaturedImageUrl();
                return $product;
            });

        // Add store image URL using ImageHelper
        $vendor->store_image_url = \App\Helpers\ImageHelper::getImageUrl($vendor->store_image);

        return Inertia::render('Store/Show', [
            'vendor' => $vendor,
            'products' => $products,
        ]);
    }

    /**
     * Show store management dashboard for vendor.
     */
    public function manage(Request $request): Response
    {
        $user = auth()->user();
        $vendor = $user->vendors()->firstOrFail();

        $filters = $request->only(['status', 'category', 'search']);
        $products = $this->productService->getVendorProducts($vendor, $filters);

        return Inertia::render('Store/Manage', [
            'vendor' => $vendor,
            'products' => $products,
            'filters' => $filters,
        ]);
    }

    /**
     * Show product creation form.
     */
    public function createProduct(): Response
    {
        $user = auth()->user();
        $vendor = $user->vendors()->firstOrFail();

        return Inertia::render('Store/Product/Create', [
            'vendor' => $vendor,
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function storeProduct(CreateProductRequest $request, Vendor $vendor)
    {
        try {
            $product = $this->productService->createProduct($vendor, $request->validated());

            return redirect()
                ->route('store.manage')
                ->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['create_product' => $e->getMessage()]);
        }
    }

    /**
     * Show product editing form.
     */
    public function editProduct(Product $product): Response
    {
        $this->authorize('update', $product);

        $product->load('variants');

        return Inertia::render('Store/Product/Edit', [
            'product' => $product,
        ]);
    }

    /**
     * Update the specified product.
     */
    public function updateProduct(UpdateProductRequest $request, Product $product)
    {
        try {
            $updatedProduct = $this->productService->updateProduct($product, $request->validated());

            return redirect()
                ->route('store.manage')
                ->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['update_product' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroyProduct(Product $product)
    {
        $this->authorize('delete', $product);

        try {
            $this->productService->deleteProduct($product);

            return redirect()
                ->route('store.manage')
                ->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['delete_product' => $e->getMessage()]);
        }
    }

    /**
     * Toggle product status (active/draft).
     */
    public function toggleProductStatus(Product $product)
    {
        $this->authorize('update', $product);

        $newStatus = $product->isActive() ? 'draft' : 'active';
        $product->update(['status' => $newStatus]);

        return back()->with('success', "Product status updated to {$newStatus}!");
    }
}
