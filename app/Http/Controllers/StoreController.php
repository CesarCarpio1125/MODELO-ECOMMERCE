<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Product;
use App\Modules\Vendor\Requests\CreateProductRequest;
use App\Modules\Vendor\Requests\QuickCreateProductRequest;
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
            ->where(function ($query) {
                $query->where('status', 'active')
                      ->orWhere('status', 'draft');
            })
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
    public function manage(Request $request, ?string $vendorId = null): Response
    {
        $user = auth()->user();

        // If vendorId is provided in route, find it; otherwise get from authenticated user
        if ($vendorId) {
            $vendor = Vendor::findOrFail($vendorId);
            // Verify ownership (ULID string comparison)
            if ((string) $vendor->user_id !== (string) $user->id) {
                abort(403, 'Unauthorized access to vendor store.');
            }
        } else {
            $vendor = $user->vendors()->first();
        }

        try {
            // Check if user has a vendor - simple validation
            if (!$vendor) {
                return redirect()
                    ->route('vendor.dashboard')
                    ->with('error', 'You need to create a vendor profile first.');
            }

            $filters = $request->only(['status', 'category', 'search']);
            $products = $this->productService->getVendorProducts($vendor, $filters);

            return Inertia::render('Store/Manage', [
                'vendor' => $vendor,
                'products' => $products,
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('vendor.dashboard')
                ->with('error', 'Error loading store management: ' . $e->getMessage());
        }
    }

    /**
     * Show product creation form.
     */
    public function createProduct(Vendor $vendor): Response
    {
        // Verify ownership
        if ((string) $vendor->user_id !== (string) auth()->id()) {
            abort(403, 'You can only create products in your own stores.');
        }

        return Inertia::render('Store/Product/Product', [
            'vendor' => $vendor,
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function storeProduct(CreateProductRequest $request, Vendor $vendor)
    {
        try {
            // Verify ownership
            if ((string) $vendor->user_id !== (string) auth()->id()) {
                abort(403, 'You can only create products in your own stores.');
            }

            $product = $this->productService->createProduct($vendor, $request->validated());

            return redirect()
                ->route('store.show', ['slug' => $vendor->store_slug])
                ->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['create_product' => $e->getMessage()]);
        }
    }

    /**
     * Quick store a newly created product with minimal fields.
     */
    public function quickStoreProduct(QuickCreateProductRequest $request, Vendor $vendor)
    {
        try {
            // Validate that the authenticated user owns this vendor
            if ($vendor->user_id !== auth()->id()) {
                abort(403, 'Unauthorized action.');
            }

            \Log::info('Quick storing product', [
                'user_id' => auth()->id(),
                'vendor_id' => $vendor->id,
                'validated_data' => $request->validated()
            ]);

            // Use all the validated fields for quick creation
            $quickData = [
                'name' => $request->validated()['name'],
                'description' => $request->validated()['description'] ?? null,
                'price' => $request->validated()['price'],
                'stock_quantity' => $request->validated()['stock_quantity'],
                'status' => 'draft', // Default to draft for quick products
                'featured_image' => $request->file('featured_image'),
            ];

            $product = $this->productService->createProduct($vendor, $quickData);

            return redirect()
                ->route('store.show', ['slug' => $vendor->store_slug])
                ->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['quick_store_product' => $e->getMessage()]);
        }
    }

    /**
     * Show product editing form.
     */
    public function editProduct(string $productId): Response
    {
        // Find product manually to avoid ULID binding issues
        $product = Product::findOrFail($productId);

        // Get the authenticated user's vendor
        $user = auth()->user();
        $userVendor = $user->vendors()->first();

        // Check if user owns this product (ULID string comparison)
        if ((string) $userVendor->id !== (string) $product->vendor_id) {
            abort(403, 'You can only edit your own products.');
        }

        $this->authorize('update', $product);

        $product->load('variants');

        return Inertia::render('Store/Product/Product', [
            'product' => $product,
        ]);
    }

    /**
     * Update the specified product.
     */
    public function updateProduct(UpdateProductRequest $request, string $productId)
    {
        // Find product manually to avoid ULID binding issues
        $product = Product::findOrFail($productId);

        // Get the authenticated user's vendor
        $user = auth()->user();
        $userVendor = $user->vendors()->first();

        // Check if user owns this product (ULID string comparison)
        if ((string) $userVendor->id !== (string) $product->vendor_id) {
            abort(403, 'You can only update your own products.');
        }

        $this->authorize('update', $product);

        try {
            $updatedProduct = $this->productService->updateProduct($product, $request->validated());

            return redirect()
                ->route('store.show', ['slug' => $product->vendor->store_slug])
                ->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['update_product' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroyProduct(string $productId)
    {
        // Find product manually to avoid ULID binding issues
        $product = Product::findOrFail($productId);

        // Get the authenticated user's vendor
        $user = auth()->user();
        $userVendor = $user->vendors()->first();

        // Check if user owns this product (ULID string comparison)
        if ((string) $userVendor->id !== (string) $product->vendor_id) {
            abort(403, 'You can only delete your own products.');
        }

        $this->authorize('delete', $product);

        try {
            $this->productService->deleteProduct($product);

            return redirect()
                ->route('store.show', ['slug' => $product->vendor->store_slug])
                ->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['delete_product' => $e->getMessage()]);
        }
    }

    /**
     * Toggle product status (active/draft).
     */
    public function toggleProductStatus(string $productId)
    {
        // Find product manually to avoid ULID binding issues
        $product = Product::findOrFail($productId);

        // Get the authenticated user's vendor
        $user = auth()->user();
        $userVendor = $user->vendors()->first();

        // Check if user owns this product (ULID string comparison)
        if ((string) $userVendor->id !== (string) $product->vendor_id) {
            abort(403, 'You can only update your own products.');
        }

        $this->authorize('update', $product);

        $newStatus = $product->isActive() ? 'draft' : 'active';
        $product->update(['status' => $newStatus]);

        return back()->with('success', "Product status updated to {$newStatus}!");
    }
}
