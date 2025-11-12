<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\orders;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrdersController extends Controller
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
        $dataOrders = new Order;
    
        $rules = [
            'total_payment' => 'required|integer',
            'sub_total' => 'required|integer',
            'tax' => 'required|integer',
            'discount' => 'required|integer',
            'total' => 'required|integer',
            'payment_method' => 'required',
            'transaction_time' => 'required|date_format:Y-m-d H:i:s',
// pastikan angka (milisecond)
            'customer_name' => 'required|string',
            'cashier_name' => 'required|string',
        ];
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal Memasukan Data',
                'data' => $validator->errors(),
            ], 400);
        }
    
        try {
            $dataOrders->total_payment = $request->total_payment;
            $dataOrders->sub_total = $request->sub_total;
            $dataOrders->tax = $request->tax;
            $dataOrders->discount = $request->discount;
            $dataOrders->total = $request->total;
            $dataOrders->total_item = $request->total_item ?? 0;
            $dataOrders->payment_method = $request->payment_method;
            $dataOrders->transaction_time = $request->transaction_time;
            $dataOrders->customer_name = $request->customer_name;
            $dataOrders->phone_number = $request->phone_number ?? 0;
            $dataOrders->cashier_name = $request->cashier_name;

            $dataOrders->save();
    
            return response()->json([
                'status' => true,
                'message' => 'Sukses Memasukan Data',
                'id' => $dataOrders->id, // penting untuk dikirim ke Flutter agar tahu order_id
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
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
