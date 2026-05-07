<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Platform;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Campaign;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    public function run(): void {

        // 1. Buat platforms
        $platforms = [
            ['name' => 'Shopee',     'slug' => 'shopee',    'color' => '#FF5722'],
            ['name' => 'Tokopedia',  'slug' => 'tokopedia', 'color' => '#4CAF50'],
            ['name' => 'TikTok Shop','slug' => 'tiktok',    'color' => '#000000'],
            ['name' => 'Instagram',  'slug' => 'instagram', 'color' => '#E91E63'],
        ];
        foreach ($platforms as $p) {
            Platform::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // 2. Buat user demo
        $user = User::updateOrCreate(
            ['email' => 'admin@pipelytcs.com'],
            [
                'name'              => 'Admin Demo',
                'password'          => Hash::make('password123'),
                'business_name'     => 'Toko Demo',
                'business_category' => 'Electronics',
            ]
        );

        // 3. Buat produk
        $productsData = [
            ['name' => 'Wireless Earbuds Pro',    'sku' => 'WEP-001', 'price' => 199000, 'stock' => 342],
            ['name' => 'Smart Watch Series 5',    'sku' => 'SWS-005', 'price' => 499000, 'stock' => 287],
            ['name' => 'Running Shoes Premium',   'sku' => 'RSP-010', 'price' => 359000, 'stock' => 234],
            ['name' => 'Laptop Stand Adjustable', 'sku' => 'LSA-003', 'price' => 149000, 'stock' => 188],
            ['name' => 'USB-C Hub 7-in-1',        'sku' => 'UCH-007', 'price' => 299000, 'stock' => 176],
        ];
        foreach ($productsData as $p) {
            Product::updateOrCreate(
                ['sku' => $p['sku'], 'user_id' => $user->id],
                array_merge($p, ['user_id' => $user->id])
            );
        }

        // 4. Buat data penjualan (200 transaksi, 30 hari ke belakang)
        $platformList = Platform::all();
        $productList  = Product::where('user_id', $user->id)->get();

        for ($i = 0; $i < 200; $i++) {
            $product  = $productList->random();
            $platform = $platformList->random();
            $qty      = rand(1, 10);
            $revenue  = $product->price * $qty;

            Sale::create([
                'user_id'          => $user->id,
                'product_id'       => $product->id,
                'platform_id'      => $platform->id,
                'product_name'     => $product->name,
                'product_category' => 'Electronics',
                'quantity'         => $qty,
                'price_per_unit'   => $product->price,
                'total_revenue'    => $revenue,
                'total_profit'     => $revenue * 0.3,
                'sold_at'          => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);
        }

        // 5. Buat data campaign
        $campaignNames = [
            'Shopee Flash Sale Campaign',
            'TikTok Live Shopping Event',
            'Instagram Stories Promo',
            'Tokopedia Banner Ads',
        ];
        foreach ($platformList as $index => $platform) {
            Campaign::create([
                'user_id'      => $user->id,
                'platform_id'  => $platform->id,
                'name'         => $campaignNames[$index] ?? 'Campaign ' . $platform->name,
                'impressions'  => rand(100000, 300000),
                'clicks'       => rand(5000, 15000),
                'conversions'  => rand(200, 600),
                'ad_spend'     => rand(2000, 5000),
                'revenue'      => rand(10000, 30000),
                'roas'         => round(rand(40, 70) / 10, 1),
                'period_start' => now()->subDays(30),
                'period_end'   => now(),
            ]);
        }
    }
}