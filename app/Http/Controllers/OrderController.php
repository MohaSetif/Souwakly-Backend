<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * Get orders based on user role.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Order::with(['product:id,name', 'affiliate:id,name', 'merchant:id,name']);

        if ($user->isAdmin()) {
            // Admin sees all orders
        } elseif ($user->isMerchant()) {
            $query->forMerchant($user->id);
        } elseif ($user->isAffiliate()) {
            $query->forAffiliate($user->id);
        } else {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->status($request->status);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $orders = $query->latest()->paginate(15);

        return response()->json($orders);
    }

    /**
     * Create a new order (Affiliate creates referral order).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);

        if (!$user->isAffiliate() && !$user->isAdmin()) {
            return response()->json(['message' => 'Only affiliates can create orders.'], 403);
        }

        $order = Order::create([
            'product_id' => $product->id,
            'affiliate_id' => $user->id,
            'merchant_id' => $product->merchant_id,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => $order->load(['product:id,name', 'merchant:id,name']),
        ], 201);
    }

    /**
     * Update order status (Merchant or Admin only).
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->merchant_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => $order,
        ]);
    }

    /**
     * Get a single order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Check if user has access to this order
        if (
            !$user->isAdmin()
            && $order->merchant_id !== $user->id
            && $order->affiliate_id !== $user->id
        ) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'data' => $order->load([
                'product:id,name,price',
                'affiliate:id,name,email',
                'merchant:id,name,email',
            ])
        ]);
    }
}
