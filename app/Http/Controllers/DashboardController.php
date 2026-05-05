<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Order_detail;
use App\Models\Product;
use App\Models\Platform;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // 1. Summary — Total Sales, Sales Growth, Units Sold, AOV
    public function summary()
    {
        $totalSalesThisMonth = Order::whereMonth('tanggal', now()->month)
            ->sum('total_harga');

        $totalSalesLastMonth = Order::whereMonth('tanggal', now()->subMonth()->month)
            ->sum('total_harga');

        $salesGrowth = $totalSalesLastMonth > 0
            ? (($totalSalesThisMonth - $totalSalesLastMonth) / $totalSalesLastMonth) * 100
            : 0;

        $unitsSold = Order_detail::whereHas('order', function ($q) {
            $q->whereMonth('tanggal', now()->month);
        })->sum('qty');

        $totalOrders = Order::whereMonth('tanggal', now()->month)->count();
        $aov = $totalOrders > 0 ? $totalSalesThisMonth / $totalOrders : 0;

        return response()->json([
            'total_sales'  => $totalSalesThisMonth,
            'sales_growth' => round($salesGrowth, 2),
            'units_sold'   => $unitsSold,
            'aov'          => round($aov, 2),
            'total_orders' => $totalOrders,
        ]);
    }

    // 2. Sales Trend 30 hari
    public function salesTrend()
    {
        $trend = Order::select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('SUM(total_harga) as total')
            )
            ->where('tanggal', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($trend);
    }

    // 3. Revenue by Platform
    public function revenueByPlatform()
    {
        $revenue = Order::select(
                'platform_id',
                DB::raw('SUM(total_harga) as total_revenue'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->with('platform:platform_id,nama')
            ->groupBy('platform_id')
            ->get();

        return response()->json($revenue);
    }

    // 4. Peak Sales Hours
    public function peakHours()
    {
        $peak = Order::select(
                DB::raw('HOUR(jam) as hour'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_harga) as total_sales')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return response()->json($peak);
    }

    // 5. Best Selling Products
    public function bestProducts()
    {
        $products = Order_detail::select(
                'product_id',
                DB::raw('SUM(qty) as units_sold'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->with('product:product_id,nama,stok,status')
            ->groupBy('product_id')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        return response()->json($products);
    }

    // 6. Low Stock Alert
    public function lowStock()
    {
        $products = Product::where('stok', '<=', 10)->get();
        return response()->json($products);
    }

    // 7. Platform Comparison
    public function platformComparison()
    {
        $platforms = Platform::select(
                'platforms.platform_id',
                'platforms.nama',
                'platforms.conversion_rate',
                'platforms.fee_percentage',
                DB::raw('SUM(orders.total_harga) as total_revenue'),
                DB::raw('COUNT(orders.order_id) as total_orders')
            )
            ->leftJoin('orders', 'platforms.platform_id', '=', 'orders.platform_id')
            ->groupBy(
                'platforms.platform_id',
                'platforms.nama',
                'platforms.conversion_rate',
                'platforms.fee_percentage'
            )
            ->get();

        return response()->json($platforms);
    }
}