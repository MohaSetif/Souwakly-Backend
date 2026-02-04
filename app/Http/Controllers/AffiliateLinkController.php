<?php

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AffiliateLinkController extends Controller
{
    /**
     * Get affiliate's referral links.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $links = AffiliateLink::where('affiliate_id', $user->id)
            ->with('product:id,name,price')
            ->paginate(15);

        return response()->json($links);
    }

    /**
     * Generate a referral link for a product.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);

        if (!$user->isAffiliate() && !$user->isAdmin()) {
            return response()->json(['message' => 'Only affiliates can generate referral links.'], 403);
        }

        // Check if link already exists
        $existingLink = AffiliateLink::where('affiliate_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingLink) {
            return response()->json([
                'message' => 'Referral link already exists.',
                'data' => $existingLink,
                'referral_url' => $existingLink->getReferralUrl(),
            ]);
        }

        $link = AffiliateLink::create([
            'affiliate_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'message' => 'Referral link generated successfully.',
            'data' => $link,
            'referral_url' => $link->getReferralUrl(),
        ], 201);
    }

    /**
     * Track click on referral link (public endpoint).
     */
    public function trackClick(string $code): JsonResponse
    {
        $link = AffiliateLink::where('code', $code)->first();

        if (!$link) {
            return response()->json(['message' => 'Invalid referral code.'], 404);
        }

        $link->incrementClicks();

        return response()->json([
            'data' => [
                'product_id' => $link->product_id,
                'product' => $link->product,
            ]
        ]);
    }

    /**
     * Get WhatsApp link for a referral.
     */
    public function getWhatsAppLink(Request $request, AffiliateLink $affiliateLink): JsonResponse
    {
        $phoneNumber = $request->query('phone');

        return response()->json([
            'whatsapp_url' => $affiliateLink->getWhatsAppLink($phoneNumber),
        ]);
    }

    /**
     * Get affiliate performance stats.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalLinks = AffiliateLink::where('affiliate_id', $user->id)->count();
        $totalClicks = AffiliateLink::where('affiliate_id', $user->id)->sum('clicks');
        $totalOrders = $user->affiliateOrders()->count();
        $confirmedOrders = $user->affiliateOrders()->where('status', 'confirmed')->count();

        return response()->json([
            'total_links' => $totalLinks,
            'total_clicks' => $totalClicks,
            'total_orders' => $totalOrders,
            'confirmed_orders' => $confirmedOrders,
        ]);
    }
}
