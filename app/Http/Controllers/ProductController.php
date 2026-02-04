<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Get all active products.
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::active()
            ->with('merchant:id,name')
            ->paginate(15);

        return response()->json($products);
    }

    /**
     * Get a single product.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('merchant:id,name');

        return response()->json(['data' => $product]);
    }

    /**
     * Create a new product (Merchant only).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
        ]);

        $user = $request->user();

        if (!$user->isMerchant() && !$user->isAdmin()) {
            return response()->json(['message' => 'Only merchants can create products.'], 403);
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'brand' => $request->brand,
            'price' => $request->price ?? 0,
            'images' => $request->images,
            'merchant_id' => $user->id,
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product,
        ], 201);
    }

    /**
     * Update a product (owner only).
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (!$product->isOwnedBy($user) && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $product->update($request->only([
            'name',
            'description',
            'brand',
            'price',
            'images',
            'status'
        ]));

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product,
        ]);
    }

    /**
     * Delete a product (owner only).
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (!$product->isOwnedBy($user) && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $product->delete();

        return response()->json(null, 204);
    }

    /**
     * Get products for the current merchant.
     */
    public function myProducts(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isMerchant()) {
            return response()->json(['message' => 'Only merchants can access this endpoint.'], 403);
        }

        $products = Product::ownedBy($user->id)
            ->withCount('orders')
            ->paginate(15);

        return response()->json($products);
    }
}
