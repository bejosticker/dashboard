<?php

namespace App\Livewire;

use App\Models\CetakProductSale as Sale;
use App\Models\CetakProductSaleItem as SaleItems;
use Livewire\Component;
use App\Models\CetakProduct;
use App\Models\Customer;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Log;

class CetakSalesForm extends Component
{
    public $products = [];
    public $items = [];
    public $paymentMethods = [];
    // Grosir & Umum dihitung per cm (luas Panjang x Lebar); Eceran per lembar
    public $prices = ['price_grosir', 'price_umum', 'price_eceran_grosir', 'price_eceran_umum'];
    public $customer = '';
    public $customer_phone = '';
    public $total = 0;
    public $discount = 0;
    public $date = '';
    public $payment_method_id = '';

    public function mount()
    {
        $this->products = CetakProduct::orderBy('name', 'asc')->get()->toArray();
        $this->paymentMethods = PaymentMethod::orderBy('name', 'asc')->get()->toArray();
        $this->customer = '';
        $this->customer_phone = '';
        $this->date = '';
        $this->discount = 0;
        $this->calculateTotal();
    }

    public function addItem()
    {
        $this->items[] = ['product_id' => '', 'panjang' => 0, 'lebar' => 0, 'lembar' => 0, 'price' => 0, 'price_type' => '', 'subtotal' => 0];
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
                $priceType = $this->items[$index]['price_type'] ?: '';
                $this->items[$index]['price'] = $priceType ? ($product[$priceType] ?? 0) : 0;
            }

            if ($field === 'price_type') {
                $product = collect($this->products)->firstWhere('id', $this->items[$index]['product_id']);
                $this->items[$index]['price'] = $product[$value] ?? 0;
            }

            $this->items[$index]['subtotal'] = $this->itemSubtotal($this->items[$index]);
        }

        $this->calculateTotal();
    }

    // Grosir/Umum: Panjang(cm) x Lebar(cm) x harga. Eceran: Lembaran x harga.
    private function itemSubtotal($item)
    {
        $price = (float)($item['price'] ?? 0);
        if (in_array($item['price_type'] ?? '', ['price_eceran_grosir', 'price_eceran_umum'])) {
            $lembar = (float)($item['lembar'] ?? 0);
            return ceil($lembar * $price);
        }
        $panjang = (float)($item['panjang'] ?? 0);
        $lebar = (float)($item['lebar'] ?? 0);
        return ceil($panjang * $lebar * $price);
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
            'items.*.product_id' => 'required|exists:cetak_products,id',
            'items.*.panjang' => 'nullable|numeric',
            'items.*.lebar' => 'nullable|numeric',
            'items.*.lembar' => 'nullable|numeric',
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
            'customer' => $this->customer ?: '-',
            'customer_phone' => $this->customer_phone ?: null,
            'discount' => $this->discount,
            'total' => $this->total,
            'payment_method_id' => $this->payment_method_id,
            'date' => $this->date
        ]);

        foreach ($this->items as $item) {
            $isEceran = in_array($item['price_type'], ['price_eceran_grosir', 'price_eceran_umum']);

            SaleItems::create([
                'cetak_product_sale_id' => $sale->id,
                'cetak_product_id' => $item['product_id'],
                'price' => $item['price'],
                'price_type' => $item['price_type'],
                'panjang' => $item['panjang'] ?: 0,
                'lebar' => $item['lebar'] ?: 0,
                'quantity' => $isEceran ? ($item['lembar'] ?: 0) : null,
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
        $this->customer = '';
        $this->customer_phone = '';
        $this->date = '';
        $this->total = 0;
    }

    public function render()
    {
        return view('livewire.cetak-sales-form');
    }
}
