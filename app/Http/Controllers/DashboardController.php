<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller {

    private $platformColors = [
        'Shopee'      => '#F97316',
        'Tokopedia'   => '#22C55E',
        'TikTok Shop' => '#000000',
        'Instagram'   => '#E1306C',
        'Website'     => '#38BDF8',
    ];

    public function overview(Request $request) {
        $userId = $request->user()->id;
        $thisMonth = Sale::where('user_id', $userId)->whereMonth('sold_at', now()->month)->whereYear('sold_at', now()->year);
        $lastMonth = Sale::where('user_id', $userId)->whereMonth('sold_at', now()->subMonth()->month)->whereYear('sold_at', now()->subMonth()->year);
        $totalSales = $thisMonth->sum('total_revenue');
        $lastSales  = $lastMonth->sum('total_revenue');
        $salesGrowth = $lastSales > 0 ? round((($totalSales - $lastSales) / $lastSales) * 100, 1) : 0;
        return response()->json([
            'total_sales'     => (float) $totalSales,
            'sales_growth'    => $salesGrowth,
            'units_sold'      => (int) $thisMonth->sum('quantity'),
            'avg_order_value' => $thisMonth->count() > 0 ? round($totalSales / $thisMonth->count(), 0) : 0,
        ]);
    }

    public function dashboardData(Request $request) {
        $userId = $request->user()->id;
        $thisMonth = Sale::where('user_id', $userId)->whereMonth('sold_at', now()->month)->whereYear('sold_at', now()->year);
        $lastMonth = Sale::where('user_id', $userId)->whereMonth('sold_at', now()->subMonth()->month)->whereYear('sold_at', now()->subMonth()->year);
        $totalSales  = $thisMonth->sum('total_revenue');
        $lastSales   = $lastMonth->sum('total_revenue');
        $salesGrowth = $lastSales > 0 ? round((($totalSales - $lastSales) / $lastSales) * 100, 1) : 0;

        $trend = Sale::where('user_id', $userId)
            ->where('sold_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(sold_at) as date, SUM(total_revenue) as sales')
            ->groupBy('date')->orderBy('date')->get()
            ->map(fn($i) => [
                'date'  => \Carbon\Carbon::parse($i->date)->locale('id')->isoFormat('D MMM'),
                'sales' => (float) $i->sales,
            ]);

        $platform = Sale::where('user_id', $userId)
            ->whereMonth('sold_at', now()->month)
            ->selectRaw('platform_name as name, SUM(total_revenue) as value')
            ->groupBy('platform_name')->orderByDesc('value')->get()
            ->map(fn($i) => [
                'name'  => $i->name,
                'value' => (float) $i->value,
                'color' => $this->platformColors[$i->name] ?? '#8B5CF6',
            ]);

        $peakHours = Sale::where('user_id', $userId)
            ->where('sold_at', '>=', now()->subDays(30))
            ->selectRaw('HOUR(sold_at) as hour, SUM(quantity) as order_count')
            ->groupBy('hour')->orderBy('hour')->get()
            ->map(function($i) {
                $h = $i->hour;
                if ($h < 8) $slot = '0-8';
                elseif ($h < 12) $slot = '8-12';
                elseif ($h < 15) $slot = '12-15';
                elseif ($h < 18) $slot = '15-18';
                elseif ($h < 21) $slot = '18-21';
                else $slot = '21-24';
                return ['time' => $slot, 'order' => (int) $i->order_count];
            });

        $topProducts = Sale::where('user_id', $userId)
            ->whereMonth('sold_at', now()->month)
            ->selectRaw('product_name as name, platform_name as platform, SUM(quantity) as units, SUM(total_revenue) as rev')
            ->groupBy('product_name', 'platform_name')->orderByDesc('rev')->limit(5)->get()
            ->map(fn($i, $idx) => [
                'id'       => $idx + 1,
                'name'     => $i->name,
                'platform' => $i->platform,
                'color'    => $this->platformColors[$i->platform] ?? '#8B5CF6',
                'units'    => (int) $i->units,
                'rev'      => (float) $i->rev,
                'growth'   => 0,
            ]);

        return response()->json([
            'summary' => [
                'totalSales'    => (float) $totalSales,
                'salesGrowth'   => $salesGrowth,
                'unitsSold'     => (int) $thisMonth->sum('quantity'),
                'avgOrderValue' => $thisMonth->count() > 0 ? round($totalSales / $thisMonth->count(), 0) : 0,
            ],
            'trend'        => $trend,
            'platform'     => $platform,
            'peak_hours'   => $peakHours,
            'top_products' => $topProducts,
        ]);
    }

    public function addSale(Request $request) {
        $request->validate([
            'platform'     => 'required|string',
            'product_name' => 'required|string',
            'kategori'     => 'nullable|string',
            'waktu'        => 'nullable|string',
            'tanggal'      => 'required|date',
            'jumlah'       => 'required|integer|min:1',
            'keuntungan'   => 'required|numeric',
        ]);
        $soldAt = $request->tanggal;
        if ($request->waktu) $soldAt = $request->tanggal . ' ' . $request->waktu . ':00';
        $qty    = $request->jumlah;
        $profit = $request->keuntungan;
        $sale = Sale::create([
            'user_id'          => $request->user()->id,
            'platform_name'    => $request->platform,
            'product_name'     => $request->product_name,
            'product_category' => $request->kategori,
            'waktu'            => $request->waktu,
            'quantity'         => $qty,
            'price_per_unit'   => $qty > 0 ? round($profit / $qty) : 0,
            'total_revenue'    => $profit,
            'total_profit'     => $profit,
            'sold_at'          => $soldAt,
        ]);
        return response()->json(['message' => 'Data berhasil ditambahkan', 'sale' => $sale], 201);
    }
}
