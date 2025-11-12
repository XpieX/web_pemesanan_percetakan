<div class="p-6 border-t border-gray-200">
    <h3 class="text-lg font-semibold mb-4">Detail Produk yang Dipesan</h3>
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2">Produk</th>
                <th class="px-4 py-2">Berat (kg)</th>
                <th class="px-4 py-2">Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $item->product->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $item->weight }}</td>
                    <td class="px-4 py-2">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
