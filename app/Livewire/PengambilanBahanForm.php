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
        $this->products = Product::select('id', 'name', 'price_agent', 'price_agent as harga', 'price_grosir_meter', 'per_roll_cm')
            ->where('stock_cm', '>', 0)
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        $this->tokos = Toko::select('id', 'name')->get()->toArray();
        $this->tokoId = '';
        $this->date = '';

        $this->items = collect($this->products)->map(function ($product) {
            return [
                'include' => false,
                'product_id' => $product['id'],
                'jumlah' => 0,
                'product_type' => 'roll',
                'harga' => $product['harga'],
                'price_agent' => $product['price_agent'],
                'price_grosir_meter' => $product['price_grosir_meter'],
                'per_roll_cm' => $product['per_roll_cm'],
                'subtotal' => 0,
            ];
        })->toArray();
        $this->calculateTotal();
    }

    public function addItem()
    {
        $this->items[] = ['include' => false, 'product_id' => '', 'jumlah' => 0, 'price_agent' => 0, 'harga' => 0, 'price_grosir_meter' => 0, 'product_type' => 'roll', 'per_roll_cm' => 0, 'subtotal' => 0];
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

            $priceType = $this->items[$index]['product_type'] ?? 'roll';
            $priceAgent = (float)($this->items[$index]['price_agent'] ?? 0);
            $priceGrosirMeter = (float)($this->items[$index]['price_grosir_meter'] ?? 0);

            $this->items[$index]['harga'] = $priceType === 'meter' ? $priceGrosirMeter : $priceAgent;

            $jumlah = (float)($this->items[$index]['jumlah'] ?? 0);

            // $this->items[$index]['subtotal'] = $jumlah * $this->items[$index]['harga'];
            // $this->calculateTotal();

            $oldSubtotal = $this->items[$index]['subtotal'] ?? 0;
            $newSubtotal = $jumlah * $this->items[$index]['harga'];

            $this->items[$index]['subtotal'] = $newSubtotal;

            // Update total incrementally instead of recalculating everything
            if ($this->items[$index]['include'] ?? true) {
                $this->total += ($newSubtotal - $oldSubtotal);
            }
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
                'product_type' => $item['product_type'] ?? 'roll',
                'jumlah' => (float)($item['jumlah'] ?? 0),
                'harga' => (float)($item['harga'] ?? 0),
                'subtotal' => (float)($item['subtotal'] ?? 0),
            ];
        })->toArray();

        $total = collect($this->items)->where('include', true)->sum('subtotal');

        $this->validate([
            'tokoId' => 'required|exists:toko,id',
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

            $product->stock_cm = ($product->stock_cm ?? 0) - ($item['product_type'] == 'roll' ? $item['jumlah'] * $product->per_roll_cm : $item['jumlah'] * 100);
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
