<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    /**
     * Retrieve all affiliates that have generated orders for this merchant.
     */
    public function affiliates(Request $request)
    {
        $user = $request->user();

        if (!$user->isMerchant() && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Must be a merchant or admin.'], 403);
        }

        // Determine if we need to filter by merchant
        $merchantId = null;
        if ($user->isAdmin()) {
            $merchantId = $request->merchant_id;
        } else {
            $merchantId = $user->id;
        }

        $query = User::role('Affiliate');

        if ($merchantId) {
            $query->whereIn('id', function ($q) use ($merchantId) {
                $q->select('affiliate_id')
                    ->from('orders')
                    ->where('merchant_id', $merchantId);
            })->withCount([
                        'affiliateOrders as orders_count' => function ($q) use ($merchantId) {
                            $q->where('merchant_id', $merchantId);
                        }
                    ]);
        } else {
            // Admin Global View: Show all affiliates who have at least one order with ANY merchant
            $query->whereHas('affiliateOrders')
                ->withCount('affiliateOrders as orders_count');
        }

        $affiliates = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $affiliates
        ]);
    }
}
