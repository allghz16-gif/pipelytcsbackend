<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seller;

class SellerController extends Controller
{
    public function index()
    {
        return response()->json(Seller::all());
    }

    public function show($id)
    {
        return response()->json(Seller::findOrFail($id));
    }

    public function store(Request $request)
    {
        $seller = Seller::create($request->all());
        return response()->json($seller, 201);
    }

    public function update(Request $request, $id)
    {
        $seller = Seller::findOrFail($id);
        $seller->update($request->all());
        return response()->json($seller);
    }

    public function destroy($id)
    {
        Seller::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted!']);
    }
}