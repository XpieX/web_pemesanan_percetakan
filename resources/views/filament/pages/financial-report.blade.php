<x-filament::page>
    {{-- 🔽 Form Filter Periode --}}
    {{ $this->form }}

    {{-- 🔽 Financial Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div class="p-4 bg-white shadow rounded-xl">
            <div class="text-sm text-gray-500">Total Pendapatan</div>
            <div class="text-2xl font-bold text-green-600">
                Rp {{ number_format($data['income'], 0, ',', '.') }}
            </div>
        </div>

        <div class="p-4 bg-white shadow rounded-xl">
            <div class="text-sm text-gray-500">Total Pengeluaran</div>
            <div class="text-2xl font-bold text-red-600">
                Rp {{ number_format($data['expense'], 0, ',', '.') }}
            </div>
        </div>

        <div class="p-4 bg-white shadow rounded-xl">
            <div class="text-sm text-gray-500">Laba / Rugi</div>
            <div class="text-2xl font-bold text-blue-600">
                Rp {{ number_format($data['profit'], 0, ',', '.') }}
            </div>
        </div>
    </div>

</x-filament::page>
