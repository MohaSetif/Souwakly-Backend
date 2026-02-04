<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get general analytics (Admin only).
     */
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'data' => [
                'total_users' => User::count(),
                'total_merchants' => User::role('Merchant')->count(),
                'total_affiliates' => User::role('Affiliate')->count(),
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'orders_by_status' => [
                    'pending' => Order::where('status', 'pending')->count(),
                    'confirmed' => Order::where('status', 'confirmed')->count(),
                    'cancelled' => Order::where('status', 'cancelled')->count(),
                ],
            ]
        ]);
    }

    /**
     * Get sales per merchant.
     */
    public function salesByMerchant(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $sales = Order::select('merchant_id', DB::raw('count(*) as total_orders'))
            ->where('status', 'confirmed')
            ->groupBy('merchant_id')
            ->with('merchant:id,name')
            ->get();

        return response()->json(['data' => $sales]);
    }

    /**
     * Get sales per affiliate.
     */
    public function salesByAffiliate(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $sales = Order::select('affiliate_id', DB::raw('count(*) as total_orders'))
            ->where('status', 'confirmed')
            ->groupBy('affiliate_id')
            ->with('affiliate:id,name')
            ->get();

        return response()->json(['data' => $sales]);
    }

    /**
     * Get sales per product.
     */
    public function salesByProduct(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isMerchant()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = Order::select('product_id', DB::raw('count(*) as total_orders'))
            ->where('status', 'confirmed')
            ->groupBy('product_id')
            ->with('product:id,name');

        // Merchant can only see their own products
        if ($user->isMerchant()) {
            $query->where('merchant_id', $user->id);
        }

        $sales = $query->get();

        return response()->json(['data' => $sales]);
    }

    /**
     * Get time-based analytics.
     */
    public function timeBasedAnalytics(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $period = $request->query('period', 'daily'); // daily, weekly, monthly

        $dateFormat = match ($period) {
            'weekly' => '%Y-%W',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $sales = Order::select(
            DB::raw("strftime('$dateFormat', created_at) as period"),
            DB::raw('count(*) as total_orders'),
            DB::raw("sum(case when status = 'confirmed' then 1 else 0 end) as confirmed_orders")
        )
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->limit(30)
            ->get();

        return response()->json(['data' => $sales]);
    }

    /**
     * Get merchant-specific analytics.
     */
    public function merchantAnalytics(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isMerchant() && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $merchantId = $user->isAdmin() ? $request->query('merchant_id', $user->id) : $user->id;

        $totalProducts = Product::where('merchant_id', $merchantId)->count();
        $totalOrders = Order::where('merchant_id', $merchantId)->count();
        $confirmedOrders = Order::where('merchant_id', $merchantId)->where('status', 'confirmed')->count();
        $topProducts = Order::select('product_id', DB::raw('count(*) as order_count'))
            ->where('merchant_id', $merchantId)
            ->where('status', 'confirmed')
            ->groupBy('product_id')
            ->orderByDesc('order_count')
            ->limit(5)
            ->with('product:id,name')
            ->get();

        return response()->json([
            'data' => [
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'confirmed_orders' => $confirmedOrders,
                'conversion_rate' => $totalOrders > 0 ? round(($confirmedOrders / $totalOrders) * 100, 2) : 0,
                'top_products' => $topProducts,
            ]
        ]);
    }
}
