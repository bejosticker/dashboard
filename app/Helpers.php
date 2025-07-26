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

            default:
                $priceType = '-';
                break;
        }

        return $priceType;
    }
