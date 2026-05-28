<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    public function campaignPerformance(Request $request)
    {
        $userId = $request->user()->id;

        $sales = Sale::where('user_id', $userId)->get();

        // ==========================================
        // SUMMARY
        // ==========================================
        $totalRevenue     = $sales->sum('total_revenue');
        $totalQuantity    = $sales->sum('quantity');
        $totalImpressions = $totalQuantity * 10;
        $totalClicks      = $totalQuantity * 3;
        $totalConversions = $sales->count();
        $totalAdSpend     = $totalRevenue * 0.2;
        $avgRoas          = $totalAdSpend > 0
            ? round($totalRevenue / $totalAdSpend, 2) : 0;
        $ctr              = $totalImpressions > 0
            ? round($totalClicks / $totalImpressions * 100, 2) : 0;

        // ==========================================
        // AD SPEND VS REVENUE PER MINGGU
        // ==========================================
        $spendRevenue = [];
        for ($w = 3; $w >= 0; $w--) {
            $start = now()->startOfWeek()->subWeeks($w);
            $end   = now()->startOfWeek()->subWeeks($w)->endOfWeek();

            $weekSales = Sale::where('user_id', $userId)
                ->whereBetween('sold_at', [$start, $end])
                ->get();

            $rev   = $weekSales->sum('total_revenue');
            $spend = $rev * 0.2;

            $spendRevenue[] = [
                'week'    => 'Week ' . (4 - $w),
                'adSpend' => round($spend, 2),
                'revenue' => round($rev, 2),
            ];
        }

        // ==========================================
        // CTR PER PLATFORM
        // ==========================================
        $ctrPlatform = $sales
            ->groupBy('platform_name')
            ->map(function ($group, $name) {
                $imp   = $group->sum('quantity') * 10;
                $click = $group->sum('quantity') * 3;
                return [
                    'platform' => $name,
                    'ctr'      => $imp > 0
                        ? round($click / $imp * 100, 2) : 0,
                ];
            })->values();

        // ==========================================
        // CAMPAIGNS TABLE (per platform)
        // ==========================================
        $campaigns = $sales
            ->groupBy('platform_name')
            ->map(function ($group, $name) {
                $imp   = $group->sum('quantity') * 10;
                $click = $group->sum('quantity') * 3;
                $conv  = $group->count();
                $rev   = $group->sum('total_revenue');
                $spend = $rev * 0.2;
                $roas  = $spend > 0 ? round($rev / $spend, 2) : 0;

                return [
                    'name'        => $name . ' Campaign',
                    'platform'    => $name,
                    'impressions' => number_format($imp),
                    'clicks'      => number_format($click),
                    'ctr'         => $imp > 0
                        ? round($click / $imp * 100, 1) . '%' : '0%',
                    'conversions' => number_format($conv),
                    'spend'       => '$' . number_format($spend, 2),
                    'revenue'     => '$' . number_format($rev, 2),
                    'roas'        => $roas . 'x',
                ];
            })->values();

        // ==========================================
        // RESPONSE
        // ==========================================
        return response()->json([
            'summary' => [
                'impressions' => [
                    'value'  => number_format($totalImpressions / 1000, 1) . 'K',
                    'growth' => '+14.2%',
                ],
                'clicks' => [
                    'value'  => number_format($totalClicks),
                    'growth' => '+11.8%',
                ],
                'ctr' => [
                    'value'  => $ctr . '%',
                    'growth' => '+0.3%',
                ],
                'conversions' => [
                    'value'  => number_format($totalConversions),
                    'growth' => '+18.5%',
                ],
                'roas' => [
                    'value'  => $avgRoas . 'x',
                    'growth' => '+0.4x',
                ],
            ],
            'spend_revenue' => $spendRevenue,
            'ctr_platform'  => $ctrPlatform,
            'funnel' => [
                'impressions' => number_format($totalImpressions / 1000, 1) . 'K',
                'clicks'      => number_format($totalClicks),
                'conversions' => number_format($totalConversions),
                'impToClick'  => $ctr . '%',
                'clickToConv' => $totalClicks > 0
                    ? round($totalConversions / $totalClicks * 100, 2) . '%' : '0%',
            ],
            'campaigns' => $campaigns,
        ]);
    }
}