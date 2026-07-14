<?php

namespace Tests\Feature;

use App\Livewire\StockAdjustmentForm;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private function bahan(int $perRollCm = 5000, int $stockCm = 46000): Product
    {
        return Product::create([
            'name' => 'Laser Putih GL',
            'per_roll_cm' => $perRollCm,
            'stock_cm' => $stockCm,
            'minimum_stock_cm' => 1500,
        ]);
    }

    public function test_jalur_normal_set_berhasil(): void
    {
        $p = $this->bahan();

        Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'set')
            ->set('bahan.0.roll', '3')
            ->call('save');

        $this->assertSame(15000, (int) $p->fresh()->stock_cm);
    }

    /** Klien pilih mode lalu klik Simpan sebelum debounce 400ms sempat mengirim nilai Roll. */
    public function test_race_debounce_mode_terkirim_nilai_belum(): void
    {
        $p = $this->bahan();

        $c = Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'set')   // wire:model.live -> langsung terkirim
            ->call('save');                // roll/meter masih '' di server

        $this->assertSame(46000, (int) $p->fresh()->stock_cm, 'stok TIDAK berubah');
        $this->assertDatabaseCount('stock_adjustments', 0);
        dump('RACE set  -> flash error: ' . var_export(session('error'), true));

        $c2 = Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'add')
            ->call('save');
        dump('RACE add  -> flash error: ' . var_export(session('error'), true));
    }

    /** Produk dengan per_roll_cm = 0 (mis. produk cetak / data lama). */
    public function test_per_roll_nol_membuat_set_roll_jadi_nol(): void
    {
        $p = $this->bahan(perRollCm: 0, stockCm: 46000);

        Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'set')
            ->set('bahan.0.roll', '3')     // 3 x 0 = 0
            ->call('save');

        dump('per_roll_cm=0, set roll=3 -> stok jadi: ' . $p->fresh()->stock_cm);
    }

    /** Nilai dikirim Livewire sebagai null, bukan string kosong. */
    public function test_nilai_null_bukan_string_kosong(): void
    {
        $p = $this->bahan();

        Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'set')
            ->set('bahan.0.roll', null)
            ->set('bahan.0.meter', null)
            ->call('save');

        dump('roll=null meter=null, mode=set -> stok jadi: ' . $p->fresh()->stock_cm
            . ' | riwayat: ' . \App\Models\StockAdjustment::count());
    }
}
