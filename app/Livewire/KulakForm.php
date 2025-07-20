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
        $this->products = Product::select('id', 'name', 'price_agent as harga')->get()->toArray();
        $this->suppliers = Supplier::select('id', 'name')->get()->toArray();
        $this->supplierId = '';
        $this->date = '';
        $this->calculateTotal();
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'jumlah' => 1, 'harga' => 0, 'subtotal' => 0];
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
        $this->total = collect($this->items)->sum('subtotal');
    }

    public function save()
    {
        $total = collect($this->items)->sum('subtotal');

        $this->validate([
            'supplierId' => 'required|exists:suppliers,id',
            'date' => 'required',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        \Log::info($this->date);
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
