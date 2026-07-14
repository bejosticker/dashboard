<?php

namespace App\Livewire;

use App\Models\CetakProduct;
use App\Models\Product;
use App\Models\StockAdjustment;
use Livewire\Component;

class StockAdjustmentForm extends Component
{
    public $date = '';
    public $search = '';
    public $bahan = [];   // baris untuk Product (bahan)
    public $cetak = [];    // baris untuk CetakProduct

    // Pesan yang tampil tepat di atas tombol Simpan (tombolnya jauh di bawah tabel,
    // alert di puncak halaman tidak pernah terlihat kasir).
    public $feedback = null;

    public function mount()
    {
        $this->date = now()->toDateString();
        $this->loadRows();
    }

    private function loadRows()
    {
        $this->bahan = Product::orderBy('name', 'asc')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'per_roll_cm' => (float) $p->per_roll_cm,
                'stock_cm' => (float) $p->stock_cm,
                'mode' => '',   // '' | 'set' | 'add' | 'sub'
                'roll' => '',
                'meter' => '',
                'note' => '',
            ];
        })->toArray();

        $this->cetak = CetakProduct::orderBy('name', 'asc')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'stock' => (float) $p->stock,
                'mode' => '',   // '' | 'set' | 'add' | 'sub'
                'value' => '',
                'note' => '',
            ];
        })->toArray();
    }

    /**
     * Nilai numerik dari input Livewire. Input angka yang kosong bisa tiba sebagai ''
     * ATAU null tergantung browser; keduanya berarti "tidak diisi", bukan nol.
     * Membedakan keduanya penting: dianggap nol, mode "set" akan mengosongkan stok.
     */
    private function num($value): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    // Hitung stok baru (cm) untuk baris bahan sesuai mode terpilih
    public function bahanNewStock($row)
    {
        $roll = $this->num($row['roll'] ?? null);
        $meter = $this->num($row['meter'] ?? null);
        $perRollCm = (float) ($row['per_roll_cm'] ?? 0);
        $stockCm = (float) ($row['stock_cm'] ?? 0);

        $delta = ($roll ?? 0) * $perRollCm + ($meter ?? 0) * 100;

        return match ($row['mode']) {
            'set' => $delta,
            'add' => $stockCm + $delta,
            'sub' => max(0, $stockCm - $delta),
            default => $stockCm,
        };
    }

    // Hitung stok baru (m) untuk baris produk cetak sesuai mode terpilih
    public function cetakNewStock($row)
    {
        $value = $this->num($row['value'] ?? null);
        $stock = (float) ($row['stock'] ?? 0);

        $delta = $value ?? 0;

        return match ($row['mode']) {
            'set' => $delta,
            'add' => $stock + $delta,
            'sub' => max(0, $stock - $delta),
            default => $stock,
        };
    }

    public function save()
    {
        $this->validate([
            'date' => 'required|date',
        ]);

        $this->feedback = null;

        $count = 0;
        $dilewati = [];   // baris yang sengaja ditolak, dilaporkan ke kasir

        foreach ($this->bahan as $row) {
            if (empty($row['mode'])) continue;

            $roll = $this->num($row['roll'] ?? null);
            $meter = $this->num($row['meter'] ?? null);

            // Mode dipilih tapi tidak ada angka yang masuk. Jangan perlakukan sebagai nol —
            // "set" tanpa nilai akan menghapus stok.
            if ($roll === null && $meter === null) {
                $dilewati[] = $row['name'] . ' (nilai belum diisi)';
                continue;
            }

            $product = Product::find($row['id']);
            if (!$product) continue;

            $perRollCm = (float) $product->per_roll_cm;

            // Roll pada produk tanpa panjang per roll selalu menghasilkan 0 cm.
            if ($roll !== null && $perRollCm <= 0) {
                $dilewati[] = $product->name . ' (panjang per roll belum diisi, pakai kolom Meter)';
                continue;
            }

            $before = (float) $product->stock_cm;
            $after = $this->bahanNewStock(array_merge($row, [
                'stock_cm' => $before,
                'per_roll_cm' => $perRollCm,
            ]));
            if ($after == $before) continue;

            $product->stock_cm = $after;
            $product->save();

            StockAdjustment::create([
                'product_type' => 'product',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'per_roll_cm' => $perRollCm,
                'mode' => $row['mode'],
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $row['note'] !== '' ? $row['note'] : null,
                'date' => $this->date,
            ]);
            $count++;
        }

        foreach ($this->cetak as $row) {
            if (empty($row['mode'])) continue;

            $value = $this->num($row['value'] ?? null);

            if ($value === null) {
                $dilewati[] = $row['name'] . ' (nilai belum diisi)';
                continue;
            }

            $product = CetakProduct::find($row['id']);
            if (!$product) continue;

            $before = (float) $product->stock;
            $after = $this->cetakNewStock(array_merge($row, ['stock' => $before]));
            if ($after == $before) continue;

            $product->stock = $after;
            $product->save();

            StockAdjustment::create([
                'product_type' => 'cetak_product',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'per_roll_cm' => null,
                'mode' => $row['mode'],
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $row['note'] !== '' ? $row['note'] : null,
                'date' => $this->date,
            ]);
            $count++;
        }

        if ($count === 0) {
            $this->feedback = [
                'type' => 'error',
                'message' => $dilewati
                    ? 'Tidak ada stok yang disimpan. Belum lengkap: ' . implode('; ', $dilewati)
                    : 'Tidak ada perubahan stok untuk disimpan.',
            ];

            return;
        }

        $pesan = $count . ' penyesuaian stok berhasil disimpan.';
        if ($dilewati) {
            $pesan .= ' Dilewati: ' . implode('; ', $dilewati);
        }

        session()->flash('success', $pesan);

        return redirect()->route('stock-adjustments');
    }

    public function render()
    {
        return view('livewire.stock-adjustment-form');
    }
}
