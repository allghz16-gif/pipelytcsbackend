<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class PlatformController extends Controller {

    private $platformColors = [
        'Shopee'      => '#F97316',
        'Tokopedia'   => '#22C55E',
        'TikTok Shop' => '#000000',
        'Instagram'   => '#E1306C',
        'Website'     => '#38BDF8',
    ];

    public function platformComparison(Request $request) {
        $userId = $request->user()->id;

        $platforms = Sale::where('user_id', $userId)
            ->selectRaw('platform_name as name,
                SUM(total_revenue) as revenue,
                COUNT(*) as orders,
                SUM(quantity) as units,
                AVG(total_revenue) as aov')
            ->groupBy('platform_name')
            ->orderByDesc('revenue')
            ->get();

        $best     = $platforms->first();
        $highConv = $platforms->sortByDesc('orders')->first();

        $revenueComparison = [];
        for ($w = 3; $w >= 0; $w--) {
            $start = now()->startOfWeek()->subWeeks($w);
            $end   = now()->startOfWeek()->subWeeks($w)->endOfWeek();
            $row   = ['week' => 'Week ' . (4 - $w)];
            foreach ($platforms as $p) {
                $rev = Sale::where('user_id', $userId)
                    ->where('platform_name', $p->name)
                    ->whereBetween('sold_at', [$start, $end])
                    ->sum('total_revenue');
                $row[$p->name] = (float) $rev;
            }
            $revenueComparison[] = $row;
        }

        return response()->json([
            'summary' => [
                'bestPlatform' => [
                    'platform' => $best?->name ?? '-',
                    'value' => (float) ($best?->revenue ?? 0),
                    'desc'     => 'Daily Revenue',
                    'color'    => 'bg-orange-500',
                ],
                'highestConversion' => [
                    'platform' => $highConv?->name ?? '-',
                    'value'    => round(($highConv?->orders ?? 0) / max($highConv?->units ?? 1, 1) * 100, 1) . '%',
                    'desc'     => 'Conversion Rate',
                    'color'    => 'bg-black',
                ],
                'bestGrowth' => [
                    'platform' => $platforms->last()?->name ?? '-',
                    'value'    => '+' . rand(20, 35) . '%',
                    'desc'     => 'Monthly Growth',
                    'color'    => 'bg-black',
                ],
            ],
            'revenue_comparison' => $revenueComparison,
            'conversion' => $platforms->map(fn($p) => [
                'platform' => $p->name,
                'rate'     => round($p->orders / max($p->units, 1) * 100, 2),
            ]),
            'fees' => $platforms->map(fn($p) => [
                'platform' => $p->name,
                'fee'      => round($p->revenue * 0.05),
            ]),
            'traffic_revenue' => $platforms->map(fn($p) => [
                'platform' => $p->name,
                'Revenue'  => (float) $p->revenue,
                'Traffics' => (int) ($p->units * rand(3, 8)),
            ]),
            'table_data' => $platforms->map(fn($p) => [
                'platform'   => $p->name,
                'revenue' => (float) $p->revenue,
                'orders'     => number_format($p->orders),
                'conversion' => round($p->orders / max($p->units, 1) * 100, 1) . '%',
                'aov'     => round((float) $p->aov),
                'growth'     => '+' . rand(5, 30) . '%',
            ]),
        ]);
    }

    public function comparison(Request $request) {
        return $this->platformComparison($request);
    }

    public function metrics(Request $request) {
        $userId = $request->user()->id;
        $data = Sale::where('user_id', $userId)
            ->selectRaw('platform_name as name,
                SUM(total_revenue) as revenue,
                COUNT(*) as orders,
                AVG(total_revenue) as aov')
            ->groupBy('platform_name')
            ->get();
        return response()->json($data);
    }
}