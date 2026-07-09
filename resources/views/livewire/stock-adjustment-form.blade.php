<div>
    @if (session()->has('success'))
        <div style="color: green; margin-bottom: 15px; padding: 10px; border: 1px solid green; background-color: #e6ffe6; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div style="color: #b02a37; margin-bottom: 15px; padding: 10px; border: 1px solid #b02a37; background-color: #ffe6e6; border-radius: 5px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="alert alert-info" role="alert" style="font-size: 13px;">
        <strong>Penyesuaian Stok</strong> mengubah stok bahan/produk cetak secara langsung untuk menyamakan dengan stok fisik.
        Tidak membuat catatan Pembelian/Pengambilan Bahan, jadi <strong>tidak mempengaruhi laporan keuangan</strong>.
        Pilih <em>Set Stok Aktual</em> untuk mengeset jumlah sebenarnya, atau <em>Tambah/Kurangi</em> untuk menyesuaikan selisih. Baris yang dibiarkan "— Lewati —" tidak diubah.
    </div>

    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-4">
            <label class="form-label">Tanggal Penyesuaian:</label>
            <input type="date" wire:model="date" class="form-control">
            @error('date') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Cari Produk:</label>
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Ketik nama produk...">
        </div>
    </div>

    {{-- ============ STOK BAHAN ============ --}}
    <h5 class="mt-2 mb-2">Stok Bahan</h5>
    <div style="max-height: 42vh; overflow-y: auto; border: 1px solid #eee; border-radius: 6px;">
        <table class="table table-sm mb-0">
            <thead style="position: sticky; top: 0; z-index: 2; background: #f5f5f9;">
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Nama Bahan</th>
                    <th style="width: 160px;">Stok Sekarang</th>
                    <th style="width: 150px;">Penyesuaian</th>
                    <th style="width: 190px;">Nilai (Roll / Meter)</th>
                    <th style="width: 160px;">Stok Baru</th>
                    <th style="width: 180px;">Alasan (opsional)</th>
                </tr>
            </thead>
            <tbody>
                @php $bahanShown = 0; @endphp
                @foreach ($bahan as $i => $item)
                    @php $match = $search === '' || stripos($item['name'], $search) !== false; @endphp
                    @if ($match)
                        @php $bahanShown++; @endphp
                        <tr wire:key="bahan-{{ $item['id'] }}">
                            <td>{{ $bahanShown }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ formatStockCm($item['stock_cm'], $item['per_roll_cm']) }}</td>
                            <td>
                                <select wire:model.live="bahan.{{ $i }}.mode" class="form-control form-control-sm">
                                    <option value="">— Lewati —</option>
                                    <option value="set">Set Stok Aktual</option>
                                    <option value="add">Tambah (+)</option>
                                    <option value="sub">Kurangi (−)</option>
                                </select>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="any" inputmode="decimal" class="form-control"
                                        placeholder="Roll" wire:model.live.debounce.400ms="bahan.{{ $i }}.roll"
                                        @if($item['mode'] === '') disabled @endif>
                                    <input type="number" step="any" inputmode="decimal" class="form-control"
                                        placeholder="Meter" wire:model.live.debounce.400ms="bahan.{{ $i }}.meter"
                                        @if($item['mode'] === '') disabled @endif>
                                </div>
                            </td>
                            <td>
                                @if ($item['mode'] !== '')
                                    <strong class="text-primary">{{ formatStockCm($this->bahanNewStock($item), $item['per_roll_cm']) }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="mis. opname"
                                    wire:model="bahan.{{ $i }}.note" @if($item['mode'] === '') disabled @endif>
                            </td>
                        </tr>
                    @endif
                @endforeach
                @if ($bahanShown === 0)
                    <tr><td colspan="7" class="text-center text-muted">Tidak ada bahan.</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- ============ STOK PRODUK CETAK ============ --}}
    <h5 class="mt-4 mb-2">Stok Produk Cetak</h5>
    <div style="max-height: 42vh; overflow-y: auto; border: 1px solid #eee; border-radius: 6px;">
        <table class="table table-sm mb-0">
            <thead style="position: sticky; top: 0; z-index: 2; background: #f5f5f9;">
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Nama Produk Cetak</th>
                    <th style="width: 160px;">Stok Sekarang</th>
                    <th style="width: 150px;">Penyesuaian</th>
                    <th style="width: 190px;">Nilai (Meter)</th>
                    <th style="width: 160px;">Stok Baru</th>
                    <th style="width: 180px;">Alasan (opsional)</th>
                </tr>
            </thead>
            <tbody>
                @php $cetakShown = 0; @endphp
                @foreach ($cetak as $i => $item)
                    @php $match = $search === '' || stripos($item['name'], $search) !== false; @endphp
                    @if ($match)
                        @php $cetakShown++; @endphp
                        <tr wire:key="cetak-{{ $item['id'] }}">
                            <td>{{ $cetakShown }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ rtrim(rtrim(number_format($item['stock'], 2, ',', '.'), '0'), ',') }} m</td>
                            <td>
                                <select wire:model.live="cetak.{{ $i }}.mode" class="form-control form-control-sm">
                                    <option value="">— Lewati —</option>
                                    <option value="set">Set Stok Aktual</option>
                                    <option value="add">Tambah (+)</option>
                                    <option value="sub">Kurangi (−)</option>
                                </select>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="any" inputmode="decimal" class="form-control"
                                        placeholder="Meter" wire:model.live.debounce.400ms="cetak.{{ $i }}.value"
                                        @if($item['mode'] === '') disabled @endif>
                                    <span class="input-group-text">m</span>
                                </div>
                            </td>
                            <td>
                                @if ($item['mode'] !== '')
                                    <strong class="text-primary">{{ rtrim(rtrim(number_format($this->cetakNewStock($item), 2, ',', '.'), '0'), ',') }} m</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="mis. opname"
                                    wire:model="cetak.{{ $i }}.note" @if($item['mode'] === '') disabled @endif>
                            </td>
                        </tr>
                    @endif
                @endforeach
                @if ($cetakShown === 0)
                    <tr><td colspan="7" class="text-center text-muted">Tidak ada produk cetak.</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
            <span class="menu-icon tf-icons bx bx-save"></span> Simpan Penyesuaian
        </button>
    </div>
</div>
