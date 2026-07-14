<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FormatStockTest extends TestCase
{
    /** Sisa di bawah 1 meter jadi desimal meter, bukan satuan cm terpisah. */
    public function test_sisa_bawah_satu_meter_jadi_desimal_meter(): void
    {
        // 43 roll (5000 cm) + 602 cm  -> dulu tampil "43 Roll 6 Meter 2 cm"
        $this->assertSame('43 Roll 6,02 Meter', formatStockCm(43 * 5000 + 602, 5000));
    }

    public function test_meter_bulat_tanpa_desimal(): void
    {
        $this->assertSame('43 Roll 6 Meter', formatStockCm(43 * 5000 + 600, 5000));
    }

    public function test_setengah_meter(): void
    {
        $this->assertSame('43 Roll 6,5 Meter', formatStockCm(43 * 5000 + 650, 5000));
    }

    public function test_roll_pas_tanpa_sisa(): void
    {
        $this->assertSame('43 Roll', formatStockCm(43 * 5000, 5000));
    }

    public function test_stok_kosong(): void
    {
        $this->assertSame('0 Roll', formatStockCm(0, 5000));
    }

    public function test_tanpa_panjang_per_roll_tampil_meter_saja(): void
    {
        $this->assertSame('2,86 Meter', formatStockCm(286, 0));
    }

    public function test_tidak_ada_satuan_cm_yang_bocor(): void
    {
        foreach ([1, 2, 99, 601, 5001, 215602] as $cm) {
            $this->assertStringNotContainsString('cm', formatStockCm($cm, 5000));
        }
    }
}
