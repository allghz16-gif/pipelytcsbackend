<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Sale;

class SettingsController extends Controller {

    // GET /api/settings
    public function index(Request $request) {
        return response()->json($request->user());
    }

    // PUT /api/settings
    public function update(Request $request) {
        $request->validate([
            'name'          => 'sometimes|string|max:255',
            'email'         => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'business_name' => 'sometimes|string|max:255',
            'phone'         => 'sometimes|string|max:20',
            'password'      => 'sometimes|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $data = $request->only(['name', 'email', 'business_name', 'phone']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Pengaturan berhasil disimpan',
            'user'    => $user
        ]);
    }

    // GET /api/settings/export-csv
    public function exportCsv(Request $request) {
        $userId = $request->user()->id;
        $sales  = Sale::where('user_id', $userId)->get();

        $csv  = "Platform,Produk,Kategori,Jumlah,Harga Satuan,Total Revenue,Total Profit,Tanggal\n";
        foreach ($sales as $s) {
            $csv .= implode(',', [
                $s->platform_name,
                $s->product_name,
                $s->product_category ?? '-',
                $s->quantity,
                $s->price_per_unit,
                $s->total_revenue,
                $s->total_profit,
                $s->sold_at,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pipelytcs-sales.csv"',
        ]);
    }

    // GET /api/settings/export-excel
    public function exportExcel(Request $request) {
        $userId = $request->user()->id;
        $sales  = Sale::where('user_id', $userId)->get();

        // Format TSV (bisa dibuka Excel)
        $tsv  = "Platform\tProduk\tKategori\tJumlah\tHarga Satuan\tTotal Revenue\tTotal Profit\tTanggal\n";
        foreach ($sales as $s) {
            $tsv .= implode("\t", [
                $s->platform_name,
                $s->product_name,
                $s->product_category ?? '-',
                $s->quantity,
                $s->price_per_unit,
                $s->total_revenue,
                $s->total_profit,
                $s->sold_at,
            ]) . "\n";
        }

        return response($tsv, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="pipelytcs-sales.xls"',
        ]);
    }

    // GET /api/settings/export-pdf
    public function exportPdf(Request $request) {
        $userId = $request->user()->id;
        $sales  = Sale::where('user_id', $userId)
            ->orderByDesc('sold_at')
            ->get();

        $rows = '';
        foreach ($sales as $s) {
            $rows .= "<tr>
                <td>{$s->platform_name}</td>
                <td>{$s->product_name}</td>
                <td>{$s->quantity}</td>
                <td>Rp " . number_format($s->total_revenue) . "</td>
                <td>{$s->sold_at}</td>
            </tr>";
        }

        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                h1 { color: #635BFF; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background: #635BFF; color: white; padding: 8px; text-align: left; }
                td { padding: 6px 8px; border-bottom: 1px solid #eee; }
                tr:nth-child(even) { background: #f9f9f9; }
            </style>
        </head>
        <body>
            <h1>Pipelytcs - Sales Report</h1>
            <p>Generated: " . now()->format('d M Y H:i') . "</p>
            <table>
                <thead>
                    <tr>
                        <th>Platform</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Revenue</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
            </table>
        </body>
        </html>";

        return response($html, 200, [
            'Content-Type'        => 'text/html',
            'Content-Disposition' => 'attachment; filename="pipelytcs-report.html"',
        ]);
    }
}