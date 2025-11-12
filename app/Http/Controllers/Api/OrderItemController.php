<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\order_item;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Validator;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dataOrderItem = new OrderItem();
        
        $rules =[
            'order_id' => 'required|integer',
            'products_id' => 'required|integer',
            'weight' => 'required',
            'price' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return response()->json([
                'status false' => false,
                'message' => 'Gagal Memasukan Data',
                'data' => $validator-> errors(),
            ], 400);
        };

        $dataOrderItem -> order_id = $request -> order_id;
        $dataOrderItem -> products_id = $request -> products_id;
        $dataOrderItem -> weight = $request -> weight;
        $dataOrderItem -> price = $request -> price;

        $post = $dataOrderItem ->save();

        return response()-> json([
            'status' => true,
            'message' => 'Sukses Memasukan Data',
            
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
