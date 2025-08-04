<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Toko;
use App\Models\PengambilanBahan;
use App\Models\PengambilanBahanItem;
use Illuminate\Support\Facades\Log;

class PengambilanBahanForm extends Component
{
    public $products = [];
    public $items = [];
    public $tokos = [];
    public $tokoId = '';
    public $total = 0;
    public $date = '';

    public function mount()
    {
        $this->products = Product::select('id', 'name', 'price_agent as harga', 'per_roll_cm')->orderBy('name', 'asc')->get()->toArray();
        $this->tokos = Toko::select('id', 'name')->get()->toArray();
        $this->tokoId = '';
        $this->date = '';

        $this->items = collect($this->products)->map(function ($product) {
            return [
                'include' => false,
                'product_id' => $product['id'],
                'jumlah' => 0,
                'type' => 'roll',
                'harga' => $product['harga'],
                'per_roll_cm' => $product['per_roll_cm'],
                'subtotal' => 0,
            ];
        })->toArray();
        $this->calculateTotal();
    }

    public function addItem()
    {
        $this->items[] = ['include' => false, 'product_id' => '', 'jumlah' => 0, 'harga' => 0, 'per_roll_cm' => 0, 'subtotal' => 0];
        $this->calculateTotal();
    }

    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->calculateTotal();
        }
    }

    public function toggleProduct($productId, $checked)
    {
        $product = collect($this->products)->firstWhere('id', $productId);

        if ($checked) {
            if (!collect($this->items)->contains('product_id', $productId)) {
                $this->items[] = [
                    'product_id' => $product['id'],
                    'jumlah' => 1,
                    'per_roll_cm' => $product['per_roll_cm'],
                    'include' => true,
                    'harga' => $product['harga'],
                    'subtotal' => $product['harga'],
                ];
            }
        } else {
            $this->items = collect($this->items)
                ->reject(fn($item) => $item['product_id'] == $productId)
                ->values()
                ->toArray();
        }
    }

    public function updated($propertyName, $value)
    {
        if (str_starts_with($propertyName, 'items.')) {
            $parts = explode('.', $propertyName);

            if (count($parts) < 3) {
                return;
            }

            $index = $parts[1];
            $field = $parts[2];

            if (!isset($this->items[$index])) {
                return;
            }

            if ($field === 'product_id') {
                $product = collect($this->products)->firstWhere('id', $this->items[$index]['product_id']);
                $this->items[$index]['harga'] = $product['harga'] ?? 0;
            }

            $perRollCm = $this->items[$index]['per_roll_cm'] ?? 0;

            $jumlah = (float)($this->items[$index]['jumlah'] ?? 0);
            $harga = (float)($this->items[$index]['harga'] ?? 0);

            $this->items[$index]['subtotal'] = $jumlah * $harga;
            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $this->total = collect($this->items)->where('include', true)->sum('subtotal');
    }

    public function save()
    {
        if (count($this->items) === 0 || !collect($this->items)->contains('include', true)) {
            session()->flash('error', 'Tidak ada item yang dipilih!');
            return;
        }
        $this->items = collect($this->items)->where('include', true)->map(function ($item) {
            return [
                'include' => $item['include'],
                'product_id' => $item['product_id'],
                'product_type' => $item['type'] ?? 'roll',
                'jumlah' => (float)($item['jumlah'] ?? 0),
                'harga' => (float)($item['harga'] ?? 0),
                'subtotal' => (float)($item['subtotal'] ?? 0),
            ];
        })->toArray();

        $total = collect($this->items)->where('include', true)->sum('subtotal');

        $this->validate([
            'tokoId' => 'required|exists:tokos,id',
            'date' => 'required',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        $pengambilan = PengambilanBahan::create([
            'toko_id' => $this->tokoId,
            'total' => $this->total,
            'date' => $this->date
        ]);

        foreach ($this->items as $item) {
            $product = Product::where('id', $item['product_id'])->first();

            PengambilanBahanItem::create([
                'pengambilan_bahan_id' => $pengambilan->id,
                'product_id' => $item['product_id'],
                'product_type' => $item['product_type'] ?? 'roll',
                'price' => $item['harga'],
                'quantity' => $item['jumlah'],
                'subtotal' => $item['subtotal'],
            ]);

            $product->stock_cm = ($product->stock_cm ?? 0) + ($item['jumlah'] * $product->per_roll_cm);
            $product->save();
        }

        session()->flash('success', 'Data berhasil disimpan!');
        $this->resetForm();
        sleep(1);
        return redirect(request()->header('Referer'));
    }

    public function resetForm()
    {
        $this->items = [];
        $this->tokoId = '';
        $this->date = '';
        $this->total = 0;
    }

    public function render()
    {
        return view('livewire.pengambilan-bahan-form');
    }
}
