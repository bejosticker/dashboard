<?php

namespace App\Livewire;

use App\Models\PaymentMethod;
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
    public $paymentMethods = [];
    public $items = [];
    public $prices = ['price_agent', 'price_grosir', 'price_umum_roll', 'price_grosir_meter', 'price_umum_meter'];
    public $customer = '';
    public $total = 0;
    public $discount = 0;
    public $date = '';
    public $payment_method_id = '';

    public function mount()
    {
        $this->products = Product::where('stock_cm', '>', 0)->orderBy('name', 'asc')->get()->toArray();
        $this->paymentMethods = PaymentMethod::orderBy('name', 'asc')->get()->toArray();
        $this->customer = '';
        $this->payment_method_id = '';
        $this->date = '';
        $this->discount = 0;
        $this->calculateTotal();
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'jumlah' => 0, 'price' => 0, 'price_type' => '', 'subtotal' => 0];
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
                $this->items[$index]['price'] = $product[$this->items[$index]['price']] ?? 0;
            }

            if ($field === 'price_type') {
                $product = collect($this->products)->firstWhere('id', $this->items[$index]['product_id']);
                $this->items[$index]['price'] = $product[$value] ?? 0;
            }

            $jumlah = (float)($this->items[$index]['jumlah'] ?? 0);
            $price = (float)($this->items[$index]['price'] ?? 0);

            $productData = Product::where('id', $this->items[$index]['product_id'])->first();
            if (in_array($this->items[$index]['price_type'], ['price_agent', 'price_grosir', 'price_umum_roll'])) {
                $this->items[$index]['subtotal'] = ceil($jumlah * $price);
            }else{
                $this->items[$index]['subtotal'] = ceil($jumlah * $price);
            }
            $this->calculateTotal();
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
            'date' => 'required',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:1',
            'items.*.price_type' => 'required',
        ]);

        $sale = Sale::create([
            'payment_method_id' => $this->payment_method_id,
            'customer' => $this->customer ?? '-',
            'discount' => $this->discount,
            'total' => $this->total,
            'date' => $this->date
        ]);

        foreach ($this->items as $item) {
            $product = Product::where('id', $item['product_id'])->first();

            SaleItems::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'price' => $item['price'],
                'price_type' => $item['price_type'],
                'quantity' => $item['jumlah'],
                'subtotal' => $item['subtotal'],
            ]);

            if (in_array($item['price_type'], ['price_agent', 'price_grosir', 'price_umum_roll'])) {
                $product->stock_cm = ($product->stock_cm ?? 0) - $item['jumlah'] * $product->per_roll_cm;
            }else{
                $product->stock_cm = ($product->stock_cm ?? 0) - $item['jumlah'] * 100;
            }

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
        $this->total = 0;
    }

    public function render()
    {
        return view('livewire.sales-form');
    }
}
