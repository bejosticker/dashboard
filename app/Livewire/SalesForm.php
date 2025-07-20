<?php

namespace App\Livewire;

use App\Models\Sale;
use App\Models\SaleItems;
use Livewire\Component;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Kulak;
use App\Models\KulakItem;
use Illuminate\Support\Facades\Log;

class SalesForm extends Component
{
    public $products = [];
    public $items = [];
    public $prices = ['price_agent', 'price_grosir', 'price_ecer_roll', 'price_ecer'];
    public $customer = '';
    public $total = 0;
    public $discount = 0;
    public $date = '';
    public $price_type = '';

    public function mount()
    {
        $this->products = Product::orderBy('name', 'asc')->get()->toArray();
        $this->customer = '';
        $this->date = '';
        $this->discount = 0;
        $this->calculateTotal();
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'jumlah' => 0, 'harga' => 0, 'subtotal' => 0];
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
                $this->items[$index]['harga'] = $product[$this->price_type] ?? 0;
            }

            $jumlah = (float)($this->items[$index]['jumlah'] ?? 0);
            $harga = (float)($this->items[$index]['harga'] ?? 0);

            $productData = Product::where('id', $this->items[$index]['product_id'])->first();
            $this->items[$index]['subtotal'] = ceil($jumlah / $productData->per_roll_cm * $harga);
            $this->calculateTotal();
        }

        if ($propertyName == 'price_type') {
            $this->items = [];
        }
    }

    public function calculateTotal()
    {
        $this->total = collect($this->items)->sum('subtotal') - $this->discount;
    }

    public function save()
    {
        $this->validate([
            'customer' => 'nullable',
            'price_type' => 'required',
            'date' => 'required',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.harga' => 'required|numeric|min:0',
        ]);

        $sale = Sale::create([
            'customer' => $this->customer ?? '-',
            'price_type' => $this->price_type,
            'discount' => $this->discount,
            'total' => $this->total,
            'date' => $this->date
        ]);

        foreach ($this->items as $item) {
            $product = Product::where('id', $item['product_id'])->first();

            SaleItems::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'price' => $item['harga'],
                'quantity' => $item['jumlah'],
                'subtotal' => $item['subtotal'],
            ]);

            $product->stock_cm = ($product->stock_cm ?? 0) - $item['jumlah'];
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
        $this->customer = '';
        $this->date = '';
        $this->price_type = '';
        $this->total = 0;
    }

    public function render()
    {
        return view('livewire.sales-form');
    }
}
