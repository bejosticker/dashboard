<?php

namespace Tests\Feature;

use App\Livewire\StockAdjustmentForm;
use App\Models\CetakProduct;
use App\Models\Product;
use App\Models\StockAdjustment;
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
            'per_roll_cm' => $perRollCm,   // 50 m per roll
            'stock_cm' => $stockCm,        // 9 Roll 10 Meter
            'minimum_stock_cm' => 1500,
        ]);
    }

    public function test_set_menulis_stok_baru(): void
    {
        $p = $this->bahan();

        Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'set')
            ->set('bahan.0.roll', '3')
            ->call('save');

        $this->assertSame(15000, (int) $p->fresh()->stock_cm);   // 3 x 5000
        $this->assertDatabaseCount('stock_adjustments', 1);
    }

    public function test_add_menambah_stok(): void
    {
        $p = $this->bahan();

        Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'add')
            ->set('bahan.0.roll', '1')
            ->call('save');

        $this->assertSame(51000, (int) $p->fresh()->stock_cm);   // 46000 + 5000
    }

    public function test_sub_mengurangi_stok_dan_tidak_minus(): void
    {
        $p = $this->bahan();

        Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'sub')
            ->set('bahan.0.roll', '99')
            ->call('save');

        $this->assertSame(0, (int) $p->fresh()->stock_cm);
    }

    /** Mode dipilih tapi nilainya belum sampai ke server. Stok TIDAK boleh tersentuh. */
    public function test_mode_tanpa_nilai_tidak_mengubah_stok_dan_memberi_pesan(): void
    {
        $p = $this->bahan();

        Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'set')
            ->call('save')
            ->assertSet('feedback.type', 'error')
            ->assertSeeHtml('nilai belum diisi');

        $this->assertSame(46000, (int) $p->fresh()->stock_cm);
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    /** Regresi: input angka kosong bisa tiba sebagai null, bukan ''. Dulu ini mengosongkan stok. */
    public function test_nilai_null_tidak_mengosongkan_stok(): void
    {
        $p = $this->bahan();

        Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'set')
            ->set('bahan.0.roll', null)
            ->set('bahan.0.meter', null)
            ->call('save');

        $this->assertSame(46000, (int) $p->fresh()->stock_cm, 'stok harus utuh, bukan 0');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    /** Regresi: produk tanpa panjang per roll. Roll x 0 = 0 cm, dulu ini mengosongkan stok. */
    public function test_roll_pada_produk_tanpa_panjang_roll_ditolak(): void
    {
        $p = $this->bahan(perRollCm: 0);

        Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'set')
            ->set('bahan.0.roll', '3')
            ->call('save')
            ->assertSet('feedback.type', 'error');

        $this->assertSame(46000, (int) $p->fresh()->stock_cm, 'stok harus utuh, bukan 0');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    /** Produk tanpa panjang roll tetap bisa disesuaikan lewat kolom Meter. */
    public function test_meter_tetap_jalan_pada_produk_tanpa_panjang_roll(): void
    {
        $p = $this->bahan(perRollCm: 0);

        Livewire::test(StockAdjustmentForm::class)
            ->set('bahan.0.mode', 'set')
            ->set('bahan.0.meter', '12')
            ->call('save');

        $this->assertSame(1200, (int) $p->fresh()->stock_cm);   // 12 m = 1200 cm
    }

    public function test_produk_cetak_ikut_tersimpan(): void
    {
        $c = CetakProduct::create([
            'name' => 'Bold UV',
            'stock' => 100,
            'price_grosir' => 35000,
            'price_umum' => 37000,
        ]);

        Livewire::test(StockAdjustmentForm::class)
            ->set('cetak.0.mode', 'add')
            ->set('cetak.0.value', '25')
            ->call('save');

        $this->assertSame(125.0, (float) $c->fresh()->stock);
        $this->assertSame('cetak_product', StockAdjustment::first()->product_type);
    }
}
