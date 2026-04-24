<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderDetail;

class Order_detailController extends Controller
{
    public function index()
    {
        return response()->json(Order_detail::all());
    }

    public function show($id)
    {
        return response()->json(Order_detail::findOrFail($id));
    }

    public function store(Request $request)
    {
        $order_detail = Order_detail::create($request->all());
        return response()->json($order_detail, 201);
    }

    public function update(Request $request, $id)
    {
        $order_detail = Order_detail::findOrFail($id);
        $order_detail->update($request->all());
        return response()->json($order_detail);
    }

    public function destroy($id)
    {
        Order_detail::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted!']);
    }
}