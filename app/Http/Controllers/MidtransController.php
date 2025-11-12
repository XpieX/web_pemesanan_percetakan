<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function getSnapToken(Request $request)
    {
        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . time(),
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $request->customer_name,
                'email' => $request->customer_email,
            ],
            'callbacks' => [
                'finish' => 'https://finish.qris.callback/'
            ],
        ];


        $snapToken = Snap::getSnapToken($params);
        return response()->json(['snap_token' => $snapToken]);
    }



    public function handleNotification(Request $request)
    {
        try {
            $notification = new Notification();

            $status = $notification->transaction_status;
            $orderId = $notification->order_id;

            Log::info("Midtrans callback: $orderId status: $status");

            // Simpan status ke DB (jika ada)
            // Order::where('order_id', $orderId)->update(['status' => $status]);

            return response()->json(['message' => 'Notification received']);
        } catch (\Exception $e) {
            Log::error("Midtrans Notification Error: " . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }
}
