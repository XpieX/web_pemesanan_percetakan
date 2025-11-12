<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Order;


class OrderExportController extends Controller
{
    public function export()
    {
        $orders = Order::with('items')->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.orders', compact('orders'));
        return $pdf->download('laporan-transaksi.pdf');
    }
    
}

