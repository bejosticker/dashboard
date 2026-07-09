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

    // Hitung stok baru (cm) untuk baris bahan sesuai mode terpilih
    public function bahanNewStock($row)
    {
        $delta = ((float) ($row['roll'] !== '' ? $row['roll'] : 0)) * (float) $row['per_roll_cm']
               + ((float) ($row['meter'] !== '' ? $row['meter'] : 0)) * 100;

        return match ($row['mode']) {
            'set' => $delta,
            'add' => (float) $row['stock_cm'] + $delta,
            'sub' => max(0, (float) $row['stock_cm'] - $delta),
            default => (float) $row['stock_cm'],
        };
    }

    // Hitung stok baru (m) untuk baris produk cetak sesuai mode terpilih
    public function cetakNewStock($row)
    {
        $delta = (float) ($row['value'] !== '' ? $row['value'] : 0);

        return match ($row['mode']) {
            'set' => $delta,
            'add' => (float) $row['stock'] + $delta,
            'sub' => max(0, (float) $row['stock'] - $delta),
            default => (float) $row['stock'],
        };
    }

    public function save()
    {
        $this->validate([
            'date' => 'required|date',
        ]);

        $count = 0;

        foreach ($this->bahan as $row) {
            if (empty($row['mode'])) continue;
            // Set butuh minimal satu input; add/sub yang delta-nya 0 otomatis terlewat (after == before)
            if ($row['mode'] === 'set' && $row['roll'] === '' && $row['meter'] === '') continue;

            $product = Product::find($row['id']);
            if (!$product) continue;

            $before = (float) $product->stock_cm;
            $after = $this->bahanNewStock(array_merge($row, [
                'stock_cm' => $before,
                'per_roll_cm' => (float) $product->per_roll_cm,
            ]));
            if ($after == $before) continue;

            $product->stock_cm = $after;
            $product->save();

            StockAdjustment::create([
                'product_type' => 'product',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'per_roll_cm' => (float) $product->per_roll_cm,
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
            if ($row['mode'] === 'set' && $row['value'] === '') continue;

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
            session()->flash('error', 'Tidak ada perubahan stok untuk disimpan.');
            return;
        }

        session()->flash('success', $count . ' penyesuaian stok berhasil disimpan.');
        sleep(1);
        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.stock-adjustment-form');
    }
}
