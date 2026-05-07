<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Campaign;
use Illuminate\Http\Request;

class PlatformController extends Controller {

    // GET /api/platforms/comparison
    public function comparison(Request $request) {
        $userId = $request->user()->id;
        $data = Sale::where('sales.user_id', $userId)
            ->whereMonth('sold_at', now()->month)
            ->join('platforms', 'sales.platform_id', '=', 'platforms.id')
            ->selectRaw('platforms.name, platforms.color,
                SUM(sales.total_revenue) as revenue,
                COUNT(sales.id) as orders,
                SUM(sales.quantity) as units')
            ->groupBy('platforms.id', 'platforms.name', 'platforms.color')
            ->orderByDesc('revenue')
            ->get();
        return response()->json($data);
    }

    // GET /api/platforms/metrics
    public function metrics(Request $request) {
        $userId = $request->user()->id;
        $data = Sale::where('sales.user_id', $userId)
            ->join('platforms', 'sales.platform_id', '=', 'platforms.id')
            ->selectRaw('platforms.name,
                SUM(sales.total_revenue) as revenue,
                COUNT(sales.id) as orders,
                AVG(sales.total_revenue) as aov')
            ->groupBy('platforms.id', 'platforms.name')
            ->get();
        return response()->json($data);
    }
}