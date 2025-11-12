<?php

namespace App\Http\Controllers;

use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;

class OrderExportExcelController extends Controller
{
    public function export()
    {
        return Excel::download(new OrdersExport, 'data penjualan.xlsx');
    }
}
