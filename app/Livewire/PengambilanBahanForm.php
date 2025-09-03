<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Toko;
use App\Models\PengambilanBahan;
use App\Models\PengambilanBahanItem;

class PengambilanBahanForm extends Component
{
    public $products = [];   // semua produk (read only)
    public $items = [];      // status user untuk tiap produk
    public $tokos = [];
    public $tokoId = '';
    public $total = 0;
    public $date = '';

    public function mount()
    {
        // preload semua produk sekali saja
        $this->products = Product::select('id', 'name', 'price_agent', 'price_grosir_meter', 'per_roll_cm', 'stock_cm')
            ->where('stock_cm', '>', 0)
            ->orderBy('name', 'asc')
            ->get()
            ->keyBy('id') // supaya bisa akses cepat via product_id
            ->toArray();

        $this->tokos = Toko::select('id', 'name')->get()->toArray();

        $this->tokoId = '';
        $this->date = '';

        // preload items tapi ringan
        $this->items = [];
        foreach ($this->products as $product) {
            $this->items[] = [
                'include' => false,
                'product_id' => $product['id'],
                'jumlah' => 0,
                'product_type' => 'roll',
                'subtotal' => 0,
            ];
        }

        $this->calculateTotal();
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'items.')) {
            $parts = explode('.', $propertyName);

            if (count($parts) < 3) return;

            $index = $parts[1];
            if (!isset($this->items[$index])) return;

            $item = &$this->items[$index];
            $product = $this->products[$item['product_id']] ?? null;

            if (!$product) return;

            $harga = $item['product_type'] === 'meter'
                ? (float)$product['price_grosir_meter']
                : (float)$product['price_agent'];

            $jumlah = (float)($item['jumlah'] ?? 0);
            $item['subtotal'] = $jumlah * $harga;

            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $this->total = 0;
        foreach ($this->items as $item) {
            if (!empty($item['include'])) {
                $this->total += $item['subtotal'];
            }
        }
    }

    public function save()
    {
        $selectedItems = array_filter($this->items, fn($item) => !empty($item['include']));

        if (empty($selectedItems)) {
            session()->flash('error', 'Tidak ada item yang dipilih!');
            return;
        }

        $this->validate([
            'tokoId' => 'required|exists:toko,id',
            'date' => 'required',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|numeric|min:1',
        ]);

        $pengambilan = PengambilanBahan::create([
            'toko_id' => $this->tokoId,
            'total' => $this->total,
            'date' => $this->date
        ]);

        foreach ($selectedItems as $item) {
            $product = $this->products[$item['product_id']] ?? null;
            if (!$product) continue;

            $harga = $item['product_type'] === 'meter'
                ? (float)$product['price_grosir_meter']
                : (float)$product['price_agent'];

            $subtotal = $item['jumlah'] * $harga;

            PengambilanBahanItem::create([
                'pengambilan_bahan_id' => $pengambilan->id,
                'product_id' => $item['product_id'],
                'product_type' => $item['product_type'],
                'price' => $harga,
                'quantity' => $item['jumlah'],
                'subtotal' => $subtotal,
            ]);

            // update stok
            $p = Product::find($item['product_id']);
            if ($p) {
                $p->stock_cm -= ($item['product_type'] === 'roll')
                    ? $item['jumlah'] * $p->per_roll_cm
                    : $item['jumlah'] * 100;
                $p->save();
            }
        }

        session()->flash('success', 'Data berhasil disimpan!');
        $this->resetForm();
        sleep(1);
        return redirect(request()->header('Referer'));
    }

    public function resetForm()
    {
        foreach ($this->items as &$item) {
            $item['include'] = false;
            $item['jumlah'] = 0;
            $item['product_type'] = 'roll';
            $item['subtotal'] = 0;
        }
        $this->tokoId = '';
        $this->date = '';
        $this->total = 0;
    }

    public function render()
    {
        return view('livewire.pengambilan-bahan-form');
    }
}
