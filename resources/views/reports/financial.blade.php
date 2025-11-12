<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h2,
        h3 {
            margin-bottom: 6px;
            color: #222;
        }

        .summary {
            margin-bottom: 20px;
        }

        .summary li {
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background-color: #f2f2f2;
        }

        table th,
        table td {
            border: 1px solid #999;
            padding: 6px 8px;
            text-align: left;
        }

        table th {
            font-weight: bold;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .highlight {
            background: #eafaea;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2>Laporan Keuangan {{ $month }}/{{ $year }}</h2>

    <h3>Ringkasan</h3>
    <ul class="summary">
        <li><strong>Total Pendapatan:</strong> Rp {{ number_format($income, 0, ',', '.') }}</li>
        <li><strong>Total Pengeluaran:</strong> Rp {{ number_format($expense, 0, ',', '.') }}</li>
        <li><strong>Laba/Rugi:</strong> Rp {{ number_format($income - $expense, 0, ',', '.') }}</li>
        <li><strong>Jumlah Transaksi:</strong> {{ $totalTransactions }}</li>
    </ul>

    <h3>Produk Yang Di Pesan</h3>
    <table border="1" cellpadding="5" cellspacing="0" width="100%">
        <thead style="background-color: #f0f0f0;">
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Total Terjual (Kg)</th>
                <th>Jumlah Transaksi</th>
                <th>Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $p)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $p->name }}</td>
                <td>{{ number_format($p->total_qty, 2, ',', '.') }} Kg</td>
                <td>{{ $p->total_orders }}</td>
                <td>Rp {{ number_format($p->total_income, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>


    <h3>Detail Pengeluaran</h3>
    <table width="100%" cellspacing="0" cellpadding="6" border="1">
        <thead style="background: #f2f2f2; font-weight: bold;">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $expense)
            <tr>
            <td>{{ $loop->iteration }}</td>
                <td>{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                <td>{{ $expense->category->name ?? '-' }}</td>
                <td>{{ $expense->description }}</td>
                <td style="text-align: right;">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>


    <h3>Detail Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Metode</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $o)
            <tr>
            <td>{{ $loop->iteration }}</td>
                <td>{{ \Carbon\Carbon::parse($o->transaction_time)->format('d/m/Y H:i') }}</td>
                <td>{{ $o->customer_name }}</td>
                <td class="text-right">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                <td>{{ $o->payment_method == 0 ? 'Cash' : 'QRIS' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>