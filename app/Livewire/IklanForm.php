<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MarketOnline;
use App\Models\OnlineAd;
use App\Models\PengambilanBahan;
use App\Models\PengambilanBahanItem;
use Illuminate\Support\Facades\Log;

class IklanForm extends Component
{
    public $products = [];
    public $items = [];
    public $tokos = [];
    public $shops = [];
    public $total = 0;
    public $date = '';

    public function mount()
    {
        $this->tokos = MarketOnline::selectRaw('id, CONCAT(name, " - ", vendor) as name')->get()->toArray();
        $this->shops = MarketOnline::selectRaw('id, CONCAT(name, " - ", vendor) as name')->get()->toArray();
        $this->date = '';

        $this->shops = collect($this->shops)->map(function ($shop) {
            return [
                'include' => false,
                'id' => $shop['id'],
                'name' => $shop['name'],
                'amount' => 0,
            ];
        })->toArray();
        $this->calculateTotal();
    }

    public function addItem()
    {
        $this->shops[] = ['include' => false, 'id' => '', 'name' => '', 'amount' => 0];
        $this->calculateTotal();
    }

    public function removeItem($index)
    {
        if (isset($this->shops[$index])) {
            unset($this->shops[$index]);
            $this->shops = array_values($this->shops);
            $this->calculateTotal();
        }
    }

    public function updated($propertyName, $value)
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = collect($this->shops)->where('include', true)->sum('amount');
    }

    public function save()
    {
        if (count($this->shops) === 0 || !collect($this->shops)->contains('include', true)) {
            session()->flash('error', 'Tidak ada item yang dipilih!');
            return;
        }
        $this->shops = collect($this->shops)->where('include', true)->map(function ($item) {
            return [
                'include' => $item['include'],
                'id' => $item['id'],
                'amount' => (float)($item['amount'] ?? 0),
            ];
        })->toArray();

        $total = collect($this->shops)->where('include', true)->sum('amount');

        $this->validate([
            'date' => 'required',
            'shops.*.id' => 'required|exists:online_markets,id',
            'shops.*.amount' => 'required|numeric|min:1',
        ]);


        foreach ($this->shops as $shop) {
            OnlineAd::create([
                'online_market_id' => $shop['id'],
                'amount' => $shop['amount'],
                'date' => $this->date
            ]);
        }

        session()->flash('success', 'Data berhasil disimpan!');
        $this->resetForm();
        sleep(1);
        return redirect(request()->header('Referer'));
    }

    public function resetForm()
    {
        $this->shops = [];
        $this->date = '';
        $this->total = 0;
    }

    public function render()
    {
        return view('livewire.iklan-form');
    }
}
