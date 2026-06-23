<?php

    function formatRupiah($angka, $prefix = 'Rp')
    {
        return $prefix . ' ' . number_format($angka, 0, ',', '.');
    }

    // Tampilan quantity item penjualan cetak (record baru: quantity+unit, record lama: panjang x lebar)
    function cetakItemQtyLabel($item) {
        if (!is_null($item->quantity)) {
            $unit = strtoupper($item->unit ?? '');
            return rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') . ' ' . $unit;
        }
        return ($item->panjang ?? 0) . ' x ' . ($item->lebar ?? 0) . ' Meter';
    }

    // Laba per item penjualan cetak
    function cetakItemLaba($item) {
        $kulak = optional($item->product)->kulak_price ?? 0;
        if (!is_null($item->quantity)) {
            // Penjualan per lembar: biaya kulak per lembar tidak diketahui, kulak dianggap 0
            if (($item->unit ?? '') === 'lembar') {
                return $item->quantity * $item->price;
            }
            return $item->quantity * ($item->price - $kulak);
        }
        // Record lama (panjang x lebar)
        return (($item->panjang ?? 0) * ($item->lebar ?? 0)) * ($item->price - $kulak);
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
