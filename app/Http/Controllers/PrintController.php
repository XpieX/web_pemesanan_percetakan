<?php

namespace App\Http\Controllers;

use App\Models\Order;
use PDF;

class PrintController extends Controller
{
    public function print($id)
    {
        $order = Order::with(['customer', 'orderItems.productSize'])->findOrFail($id);

        $pdf = PDF::loadView('pdf.order', compact('order'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream("order-{$order->id}.pdf");
        // stream = tampilkan → user tinggal klik print
    }
}
