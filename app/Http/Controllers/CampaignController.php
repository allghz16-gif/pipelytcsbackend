<?php
namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller {

    // GET /api/campaigns
    public function index(Request $request) {
        $userId = $request->user()->id;
        $data = Campaign::where('user_id', $userId)
            ->with('platform')
            ->orderByDesc('created_at')
            ->get();
        return response()->json($data);
    }

    // GET /api/campaigns/funnel
    public function funnel(Request $request) {
        $userId = $request->user()->id;
        $data = Campaign::where('user_id', $userId)
            ->selectRaw('
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(conversions) as total_conversions,
                SUM(ad_spend) as total_spend,
                SUM(revenue) as total_revenue,
                AVG(roas) as avg_roas,
                CASE WHEN SUM(impressions) > 0
                    THEN ROUND(SUM(clicks)/SUM(impressions)*100, 2)
                    ELSE 0 END as ctr,
                CASE WHEN SUM(clicks) > 0
                    THEN ROUND(SUM(conversions)/SUM(clicks)*100, 2)
                    ELSE 0 END as conversion_rate
            ')
            ->first();
        return response()->json($data);
    }
}