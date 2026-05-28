<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SalesInsightController extends Controller {

    public function index(Request $request) {
        $userId = $request->user()->id;

        $thisMonth   = Sale::where('user_id', $userId)->whereMonth('sold_at', now()->month);
        $lastMonth   = Sale::where('user_id', $userId)->whereMonth('sold_at', now()->subMonth()->month);
        $thisRevenue = $thisMonth->sum('total_revenue');
        $lastRevenue = $lastMonth->sum('total_revenue');
        $thisOrders  = $thisMonth->count();
        $lastOrders  = $lastMonth->count();
        $aov         = $thisOrders > 0 ? round($thisRevenue / $thisOrders) : 0;
        $lastAov     = $lastOrders > 0 ? round($lastRevenue / $lastOrders) : 0;
        $aovGrowth   = $lastAov > 0 ? round((($aov - $lastAov) / $lastAov) * 100, 1) : 0;
        $growthRate  = $lastRevenue > 0 ? round((($thisRevenue - $lastRevenue) / $lastRevenue) * 100, 1) : 0;

        // Revenue per minggu
        $revenue = [];
        for ($w = 3; $w >= 0; $w--) {
            $start     = now()->startOfWeek()->subWeeks($w);
            $end       = now()->startOfWeek()->subWeeks($w)->endOfWeek();
            $rev       = Sale::where('user_id', $userId)->whereBetween('sold_at', [$start, $end])->sum('total_revenue');
            $revenue[] = ['week' => 'Week ' . (4 - $w), 'revenue' => (float) $rev];
        }

        // Revenue by category
        $category = Sale::where('user_id', $userId)
            ->whereMonth('sold_at', now()->month)
            ->selectRaw('product_category as category, SUM(total_revenue) as revenue')
            ->groupBy('product_category')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn($i) => [
                'category' => $i->category ?? 'Lainnya',
                'revenue'  => (float) $i->revenue,
            ]);

        // Retention trend 6 bulan
        $retention = [];
        for ($m = 5; $m >= 0; $m--) {
            $month       = now()->subMonths($m);
            $retention[] = [
                'month' => $month->locale('id')->isoFormat('MMM'),
                'rate'  => rand(70, 90),
            ];
        }

        // Table
        $table = [
            [
                'metric'    => 'Total Revenue',
                'thisMonth' => 'Rp ' . number_format($thisRevenue),
                'lastMonth' => 'Rp ' . number_format($lastRevenue),
                'change'    => ($growthRate >= 0 ? '+' : '') . $growthRate . '%',
            ],
            [
                'metric'    => 'Total Orders',
                'thisMonth' => number_format($thisOrders),
                'lastMonth' => number_format($lastOrders),
                'change'    => $lastOrders > 0
                    ? ($thisOrders >= $lastOrders ? '+' : '') . round((($thisOrders - $lastOrders) / max($lastOrders, 1)) * 100, 1) . '%'
                    : '0%',
            ],
            [
                'metric'    => 'Average Order Value',
                'thisMonth' => 'Rp ' . number_format($aov),
                'lastMonth' => 'Rp ' . number_format($lastAov),
                'change'    => ($aovGrowth >= 0 ? '+' : '') . $aovGrowth . '%',
            ],
        ];

        return response()->json([
            'metrics' => [
            'avgOrderValue'   => $aov,  // ← kirim angka, bukan string
            'aovGrowth'       => ($aovGrowth >= 0 ? '+' : '') . $aovGrowth . '%',
            'retention'       => '82.4',
            'retentionGrowth' => '+3.1%',
            'repeatRate'      => '45.6',
            'repeatGrowth'    => '+2.8%',
            'growthRate'      => $growthRate,
            'growthPeriod'    => 'Monthly',
           ],
            'revenue'   => $revenue,
            'category'  => $category,
            'retention' => $retention,
            'table'     => $table,
        ]);
    }
}