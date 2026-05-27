<?php
namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Sale;
use Illuminate\Http\Request;

class CampaignController extends Controller {

    // GET /api/campaign-performance
    public function campaignPerformance(Request $request) {
        $userId = $request->user()->id;

        $campaigns = Campaign::where('user_id', $userId)
            ->with('platform')
            ->get();

        // Summary cards
        $totalImpressions = $campaigns->sum('impressions');
        $totalClicks      = $campaigns->sum('clicks');
        $totalConversions = $campaigns->sum('conversions');
        $totalSpend       = $campaigns->sum('ad_spend');
        $totalRevenue     = $campaigns->sum('revenue');
        $avgRoas          = $campaigns->avg('roas');
        $ctr              = $totalImpressions > 0
            ? round($totalClicks / $totalImpressions * 100, 2) : 0;

        // Ad spend vs revenue per minggu
        $spendRevenue = [];
        for ($w = 3; $w >= 0; $w--) {
            $start = now()->startOfWeek()->subWeeks($w);
            $end   = now()->startOfWeek()->subWeeks($w)->endOfWeek();
            $spendRevenue[] = [
                'week'    => 'Week ' . (4 - $w),
                'adSpend' => (float) Campaign::where('user_id', $userId)
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('ad_spend'),
                'revenue' => (float) Campaign::where('user_id', $userId)
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('revenue'),
            ];
        }

        // CTR per platform
        $ctrPlatform = $campaigns->groupBy('platform.name')
            ->map(function($group, $name) {
                $imp   = $group->sum('impressions');
                $click = $group->sum('clicks');
                return [
                    'platform' => $name ?? 'Unknown',
                    'ctr'      => $imp > 0 ? round($click / $imp * 100, 2) : 0,
                ];
            })->values();

        // Campaign table
        $campaignTable = $campaigns->map(fn($c) => [
            'name'        => $c->name,
            'platform'    => $c->platform?->name ?? '-',
            'impressions' => number_format($c->impressions),
            'clicks'      => number_format($c->clicks),
            'ctr'         => $c->impressions > 0
                ? round($c->clicks / $c->impressions * 100, 1) . '%' : '0%',
            'conversions' => number_format($c->conversions),
            'spend'       => '$' . number_format($c->ad_spend),
            'revenue'     => '$' . number_format($c->revenue),
            'roas'        => $c->roas . 'x',
        ]);

        return response()->json([
            'summary' => [
                'impressions'  => ['value' => number_format($totalImpressions / 1000, 0) . 'K', 'growth' => '+14.2%'],
                'clicks'       => ['value' => number_format($totalClicks), 'growth' => '+11.8%'],
                'ctr'          => ['value' => $ctr . '%', 'growth' => '+0.3%'],
                'conversions'  => ['value' => number_format($totalConversions), 'growth' => '+18.5%'],
                'roas'         => ['value' => round($avgRoas, 2) . 'x', 'growth' => '+0.4x'],
            ],
            'spend_revenue' => $spendRevenue,
            'ctr_platform'  => $ctrPlatform,
            'funnel' => [
                'impressions' => number_format($totalImpressions / 1000, 0) . 'K',
                'clicks'      => number_format($totalClicks),
                'conversions' => number_format($totalConversions),
                'impToClick'  => $ctr . '%',
                'clickToConv' => $totalClicks > 0
                    ? round($totalConversions / $totalClicks * 100, 2) . '%' : '0%',
            ],
            'campaigns' => $campaignTable,
        ]);
    }

    // GET /api/campaigns (lama)
    public function index(Request $request) {
        return $this->campaignPerformance($request);
    }

    // GET /api/campaigns/funnel (lama)
    public function funnel(Request $request) {
        $userId = $request->user()->id;
        $data = Campaign::where('user_id', $userId)
            ->selectRaw('SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(conversions) as total_conversions')
            ->first();
        return response()->json($data);
    }
}