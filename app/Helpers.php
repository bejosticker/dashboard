<?php

    function formatRupiah($angka, $prefix = 'Rp')
    {
        return $prefix . ' ' . number_format($angka, 0, ',', '.');
    }

    function convertPriceType($type) {
        $priceType = '';
        switch ($type) {
            case 'price_umum':
                $priceType = 'Harga Umum';
                break;

            case 'price_agent':
                $priceType = 'Harga Agen';
                break;
            
            case 'price_grosir':
                $priceType = 'Harga Grosir';
                break;

            case 'price_umum_roll':
                $priceType = 'Harga Roll Umum';
                break;

            case 'price_grosir_meter':
                $priceType = 'Harga Meteran Grosir';
                break;

            case 'price_umum_meter':
                $priceType = 'Harga Meteran Umum';
                break;

            case 'price_eceran_grosir':
                $priceType = 'Harga Eceran Grosir';
                break;

            case 'price_eceran_umum':
                $priceType = 'Harga Eceran Umum';
                break;

            default:
                $priceType = '-';
                break;
        }

        return $priceType;
    }

    // Apakah jenis harga cetak berbasis lembar (eceran)?
    function cetakIsEceran($priceType) {
        return in_array($priceType, ['price_eceran_grosir', 'price_eceran_umum']);
    }

    // Label kuantitas item penjualan cetak: eceran -> "N Lembar"; grosir/umum -> "P x L cm"
    function cetakItemQtyLabel($item) {
        if (cetakIsEceran($item->price_type)) {
            $q = rtrim(rtrim(number_format($item->quantity ?? 0, 2, ',', '.'), '0'), ',');
            return $q . ' Lembar';
        }
        return ($item->panjang ?? 0) . ' x ' . ($item->lebar ?? 0) . ' cm';
    }

    // Laba per item penjualan cetak (grosir/umum berbasis luas; eceran berbasis lembar, kulak/lembar tak diketahui -> 0)
    function cetakItemLaba($item) {
        $kulak = optional($item->product)->kulak_price ?? 0;
        if (cetakIsEceran($item->price_type)) {
            return ($item->quantity ?? 0) * $item->price;
        }
        return (($item->panjang ?? 0) * ($item->lebar ?? 0)) * ($item->price - $kulak);
    }

    // Format stok bahan (cm) menjadi "X Roll Y Meter Z cm" sesuai panjang per roll.
    function formatStockCm($cm, $perRollCm) {
        $cm = (int) round((float) $cm);
        $perRollCm = (int) round((float) $perRollCm);

        if ($perRollCm > 0) {
            $roll = intdiv($cm, $perRollCm);
            $sisa = $cm % $perRollCm;
            $meter = intdiv($sisa, 100);
            $sisaCm = $sisa % 100;

            $label = "{$roll} Roll";
            if ($meter > 0) $label .= " {$meter} Meter";
            if ($sisaCm > 0) $label .= " {$sisaCm} cm";
            return $label;
        }

        // Tanpa panjang per roll: tampilkan dalam meter
        $meter = $cm / 100;
        return rtrim(rtrim(number_format($meter, 2, ',', '.'), '0'), ',') . ' Meter';
    }

    // Label stok utk riwayat penyesuaian: bahan (cm -> Roll/Meter), produk cetak (m).
    function stockLabel($type, $value, $perRollCm = null) {
        if ($type === 'product') {
            return formatStockCm($value, $perRollCm);
        }
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') . ' m';
    }
