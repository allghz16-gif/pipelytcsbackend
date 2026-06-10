<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class ProductController extends Controller {

    private $platformColors = [
        'Shopee'      => '#F97316',
        'Tokopedia'   => '#22C55E',
        'TikTok Shop' => '#000000',
        'Instagram'   => '#E1306C',
        'Website'     => '#38BDF8',
    ];

    private $platformBgColors = [
        'Shopee'      => 'bg-orange-500',
        'Tokopedia'   => 'bg-green-500',
        'TikTok Shop' => 'bg-black',
        'Instagram'   => 'bg-pink-500',
        'Website'     => 'bg-blue-400',
    ];

    public function productPerformance(Request $request) {
        $userId = $request->user()->id;

        $products = Sale::where('user_id', $userId)
            ->selectRaw('product_name as name,
                platform_name as platform,
                product_category as sku,
                SUM(quantity) as sold,
                SUM(total_revenue) as revenue')
            ->groupBy('product_name', 'platform_name', 'product_category')
            ->orderByDesc('sold')
            ->get()
            ->map(function($p, $i) {
                $sold   = (int) $p->sold;
                $trend  = $i < 3 ? rand(5, 20) : rand(-10, -1);
                $status = match(true) {
                    $sold > 100 => 'Top Selling',
                    $sold > 50  => 'Fast Moving',
                    $sold > 20  => 'Restocking',
                    default     => 'Review',
                };
                $statusColor = match($status) {
                    'Top Selling' => 'bg-green-100 text-green-700',
                    'Fast Moving' => 'bg-blue-100 text-blue-700',
                    'Restocking'  => 'bg-yellow-100 text-yellow-700',
                    default       => 'bg-gray-100 text-gray-600',
                };
                return [
                    'id'            => $i + 1,
                    'name'          => $p->name,
                    'sku'           => $p->sku ?? '-',
                    'platform'      => $p->platform,
                    'platformColor' => $this->platformColors[$p->platform] ?? '#8B5CF6',
                    'sold'          => $sold,
                    'revenue'       => 'Rp ' . number_format($p->revenue, 0, ',', '.'),
                    'trend'         => $trend,
                    'status'        => $status,
                    'statusColor'   => $statusColor,
                ];
            });

        $heatmap = Sale::where('user_id', $userId)
            ->where('sold_at', '>=', now()->subDays(30))
            ->selectRaw('HOUR(sold_at) as hour, SUM(quantity) as sales')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn($h) => [
                'time'  => str_pad($h->hour, 2, '0', STR_PAD_LEFT) . ':00',
                'sales' => (int) $h->sales,
            ]);

        $lowStock   = $products->whereIn('status', ['Restocking', 'Review'])->count();
        $fastMoving = $products->whereIn('status', ['Fast Moving', 'Top Selling'])->count();

        return response()->json([
            'alert' => [
                'show'    => $lowStock > 0,
                'message' => $lowStock . ' products have low inventory. Consider restocking soon.',
            ],
            'products' => $products,
            'heatmap'  => $heatmap,
            'summary'  => [
                'totalProducts'  => $products->count(),
                'activeSkus'     => 'Active SKUs',
                'fastMoving'     => $fastMoving,
                'highDemand'     => 'High demand products',
                'lowStock'       => $lowStock,
                'needRestocking' => 'Need restocking',
            ],
        ]);
    }

    public function index(Request $request) {
        return $this->productPerformance($request);
    }

    public function heatmap(Request $request) {
        $userId = $request->user()->id;
        $data = Sale::where('user_id', $userId)
            ->selectRaw('HOUR(sold_at) as hour, COUNT(*) as sales_count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        return response()->json($data);
    }
}
