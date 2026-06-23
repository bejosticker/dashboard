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
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class SalesForm extends Component
{
    public $products = [];
    public $paymentMethods = [];
    public $customers = [];
    public $items = [];
    public $prices = ['price_agent', 'price_grosir', 'price_umum_roll', 'price_grosir_meter', 'price_umum_meter'];
    public $customer = '';
    public $customer_phone = '';
    public $total = 0;
    public $discount = 0;
    public $date = '';
    public $payment_method_id = '';

    public function mount()
    {
        $this->products = Product::where('stock_cm', '>', 0)->orderBy('name', 'asc')->get()->toArray();
        $this->paymentMethods = PaymentMethod::orderBy('name', 'asc')->get()->toArray();
        $this->customers = Customer::orderBy('name', 'asc')->get(['name', 'phone'])->toArray();
        $this->customer = '';
        $this->customer_phone = '';
        $this->payment_method_id = '';
        $this->date = '';
        $this->discount = 0;
        $this->calculateTotal();
    }

    // Saat nomor WA cocok dengan pelanggan yang sudah ada, otomatis isi namanya
    public function updatedCustomerPhone($value)
    {
        $existing = collect($this->customers)->firstWhere('phone', $value);
        if ($existing) {
            $this->customer = $existing['name'] ?? $this->customer;
        }
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
        }
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = collect($this->items)->sum('subtotal') - $this->discount;
    }

    public function save()
    {
        $this->validate([
            'customer' => 'nullable',
            'customer_phone' => 'nullable|regex:/^08[0-9]{7,13}$/',
            'date' => 'required',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:1',
            'items.*.price_type' => 'required',
        ], [
            'customer_phone.regex' => 'Nomor WA harus berformat 08xxxxxxxxx.',
        ]);

        // Simpan / perbarui database pelanggan (nomor WA sebagai identitas unik)
        if (!empty($this->customer_phone)) {
            Customer::updateOrCreate(
                ['phone' => $this->customer_phone],
                ['name' => $this->customer ?: null]
            );
        }

        $sale = Sale::create([
            'payment_method_id' => $this->payment_method_id,
            'customer' => $this->customer ?? '-',
            'customer_phone' => $this->customer_phone ?: null,
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
        $this->customer_phone = '';
        $this->date = '';
        $this->total = 0;
    }

    public function render()
    {
        return view('livewire.sales-form');
    }
}
