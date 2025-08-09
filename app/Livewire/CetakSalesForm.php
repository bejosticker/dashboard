<?php

namespace App\Livewire;

use App\Models\CetakProductSale as Sale;
use App\Models\CetakProductSaleItem as SaleItems;
use Livewire\Component;
use App\Models\CetakProduct;
use App\Models\Supplier;
use App\Models\Kulak;
use App\Models\KulakItem;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Log;

class CetakSalesForm extends Component
{
    public $products = [];
    public $items = [];
    public $paymentMethods = [];
    public $prices = ['price_grosir', 'price_umum'];
    public $total = 0;
    public $discount = 0;
    public $date = '';
    public $payment_method_id = '';

    public function mount()
    {
        $this->products = CetakProduct::orderBy('name', 'asc')->get()->toArray();
        $this->paymentMethods = PaymentMethod::orderBy('name', 'asc')->get()->toArray();
        $this->date = '';
        $this->discount = 0;
        $this->calculateTotal();
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'panjang' => 0, 'lebar' => 0, 'price' => 0, 'price_type' => '', 'subtotal' => 0];
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

            $panjang = (float)($this->items[$index]['panjang'] ?? 0);
            $lebar = (float)($this->items[$index]['lebar'] ?? 0);
            $price = (float)($this->items[$index]['price'] ?? 0);

            $this->items[$index]['subtotal'] = ceil($panjang * $lebar * $price);
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
            'date' => 'required',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'items.*.product_id' => 'required|exists:cetak_products,id',
            'items.*.panjang' => 'required|numeric',
            'items.*.lebar' => 'required|numeric',
            'items.*.price' => 'required|numeric|min:1',
            'items.*.price_type' => 'required',
        ]);

        $sale = Sale::create([
            'discount' => $this->discount,
            'total' => $this->total,
            'payment_method_id' => $this->payment_method_id,
            'date' => $this->date
        ]);

        foreach ($this->items as $item) {
            SaleItems::create([
                'cetak_product_sale_id' => $sale->id,
                'cetak_product_id' => $item['product_id'],
                'price' => $item['price'],
                'price_type' => $item['price_type'],
                'panjang' => $item['panjang'],
                'lebar' => $item['lebar'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        session()->flash('success', 'Data berhasil disimpan!');
        $this->resetForm();
        sleep(1);
        return redirect(request()->header('Referer'));
    }

    public function resetForm()
    {
        $this->items = [];
        $this->date = '';
        $this->total = 0;
    }

    public function render()
    {
        return view('livewire.cetak-sales-form');
    }
}
