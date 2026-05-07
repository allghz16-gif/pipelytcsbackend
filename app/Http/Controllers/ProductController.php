<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class ProductController extends Controller {

    // GET /api/products
    public function index(Request $request) {
        $userId = $request->user()->id;
        $data = Sale::where('sales.user_id', $userId)
            ->whereMonth('sold_at', now()->month)
            ->join('platforms', 'sales.platform_id', '=', 'platforms.id')
            ->selectRaw('product_name, platforms.name as platform,
                platforms.color as platform_color,
                SUM(quantity) as units_sold,
                SUM(total_revenue) as revenue')
            ->groupBy('product_name', 'platforms.name', 'platforms.color')
            ->orderByDesc('revenue')
            ->get();
        return response()->json($data);
    }

    // GET /api/products/heatmap
    public function heatmap(Request $request) {
        $userId = $request->user()->id;
        $data = Sale::where('user_id', $userId)
            ->where('sold_at', '>=', now()->subDays(30))
            ->selectRaw('HOUR(sold_at) as hour, COUNT(*) as sales_count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        return response()->json($data);
    }
}