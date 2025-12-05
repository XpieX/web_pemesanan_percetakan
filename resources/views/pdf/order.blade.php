<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td, th { border: 1px solid #333; padding: 6px; }
        h2 { text-align:center; }
    </style>
</head>
<body>

    <h2>Invoice #{{ $order->id }}</h2>

    <p><strong>Customer:</strong> {{ $order->customer->name }}</p>
    <p><strong>Tanggal:</strong> {{ $order->created_at->format('d-m-Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Barang</th>
                <th>Ukuran</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>
                        @if($item->width && $item->length)
                            {{ (int)$item->width }} x {{ (int)$item->length }} cm
                        @else
                            {{ $item->productSize->name ?? '-' }}
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
