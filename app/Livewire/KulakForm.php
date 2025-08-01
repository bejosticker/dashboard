<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Kulak;
use App\Models\KulakItem;
use Illuminate\Support\Facades\Log;

class KulakForm extends Component
{
    public $products = [];
    public $items = [];
    public $suppliers = [];
    public $supplierId = '';
    public $total = 0;
    public $date = '';

    public function mount()
    {
        $this->products = Product::select('id', 'name', 'price_kulak as harga')->orderBy('name', 'asc')->get()->toArray();
        $this->suppliers = Supplier::select('id', 'name')->get()->toArray();
        $this->supplierId = '';
        $this->date = '';

        $this->items = collect($this->products)->map(function ($product) {
            return [
                'include' => false,
                'product_id' => $product['id'],
                'jumlah' => 0,
                'harga' => $product['harga'],
                'subtotal' => 0,
            ];
        })->toArray();
        $this->calculateTotal();
    }

    public function addItem()
    {
        $this->items[] = ['include' => false, 'product_id' => '', 'jumlah' => 0, 'harga' => 0, 'subtotal' => 0];
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
                'jumlah' => (float)($item['jumlah'] ?? 0),
                'harga' => (float)($item['harga'] ?? 0),
                'subtotal' => (float)($item['subtotal'] ?? 0),
            ];
        })->toArray();

        $total = collect($this->items)->where('include', true)->sum('subtotal');

        $this->validate([
            'supplierId' => 'required|exists:suppliers,id',
            'date' => 'required',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        $kulak = Kulak::create([
            'supplier_id' => $this->supplierId,
            'total' => $this->total,
            'date' => $this->date
        ]);

        foreach ($this->items as $item) {
            $product = Product::where('id', $item['product_id'])->first();

            KulakItem::create([
                'kulak_id' => $kulak->id,
                'product_id' => $item['product_id'],
                'price' => $item['harga'],
                'rolls' => $item['jumlah'],
                'per_roll_cm' => $product->per_roll_cm,
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
        $this->supplierId = '';
        $this->date = '';
        $this->total = 0;
    }

    public function render()
    {
        return view('livewire.kulak-form');
    }
}
