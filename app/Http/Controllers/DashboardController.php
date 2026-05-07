<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller {

    // GET /api/dashboard/overview
    public function overview(Request $request) {
        $userId = $request->user()->id;

        $thisMonth = Sale::where('user_id', $userId)
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year);

        $lastMonth = Sale::where('user_id', $userId)
            ->whereMonth('sold_at', now()->subMonth()->month)
            ->whereYear('sold_at', now()->subMonth()->year);

        $totalSales  = $thisMonth->sum('total_revenue');
        $lastSales   = $lastMonth->sum('total_revenue');
        $salesGrowth = $lastSales > 0
            ? round((($totalSales - $lastSales) / $lastSales) * 100, 1)
            : 0;

        return response()->json([
            'total_sales'     => $totalSales,
            'sales_growth'    => $salesGrowth,
            'units_sold'      => $thisMonth->sum('quantity'),
            'avg_order_value' => $thisMonth->count() > 0
                ? round($totalSales / $thisMonth->count(), 0) : 0,
        ]);
    }

    // GET /api/dashboard/sales-trend
    public function salesTrend(Request $request) {
        $userId = $request->user()->id;
        $data = Sale::where('user_id', $userId)
            ->where('sold_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(sold_at) as date, SUM(total_revenue) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        return response()->json($data);
    }

    // GET /api/dashboard/revenue-by-platform
    public function revenueByPlatform(Request $request) {
        $userId = $request->user()->id;
        $data = Sale::where('sales.user_id', $userId)
            ->whereMonth('sold_at', now()->month)
            ->join('platforms', 'sales.platform_id', '=', 'platforms.id')
            ->selectRaw('platforms.name, platforms.color, SUM(sales.total_revenue) as revenue')
            ->groupBy('platforms.id', 'platforms.name', 'platforms.color')
            ->orderByDesc('revenue')
            ->get();
        return response()->json($data);
    }

    // GET /api/dashboard/top-products
    public function topProducts(Request $request) {
        $userId = $request->user()->id;
        $data = Sale::where('sales.user_id', $userId)
            ->whereMonth('sold_at', now()->month)
            ->join('platforms', 'sales.platform_id', '=', 'platforms.id')
            ->selectRaw('product_name, platforms.name as platform,
                platforms.color as platform_color,
                SUM(quantity) as units, SUM(total_revenue) as revenue')
            ->groupBy('product_name', 'platforms.name', 'platforms.color')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();
        return response()->json($data);
    }

    // POST /api/dashboard/add-sale
    public function addSale(Request $request) {
        $validated = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'platform_id'      => 'required|exists:platforms,id',
            'product_name'     => 'required|string',
            'product_category' => 'nullable|string',
            'quantity'         => 'required|integer|min:1',
            'price_per_unit'   => 'required|numeric',
            'total_profit'     => 'required|numeric',
            'sold_at'          => 'required|date',
        ]);
        $validated['user_id']       = $request->user()->id;
        $validated['total_revenue'] = $validated['quantity'] * $validated['price_per_unit'];
        $sale = Sale::create($validated);
        return response()->json(['message' => 'Data berhasil ditambahkan', 'sale' => $sale], 201);
    }
}