<?php

    function formatRupiah($angka, $prefix = 'Rp')
    {
        return $prefix . ' ' . number_format($angka, 0, ',', '.');
    }

    function convertPriceType($type) {
        $priceType = '';
        switch ($type) {
            case 'price_agent':
                $priceType = 'Harga Agen';
                break;
            
            case 'price_grosir':
                $priceType = 'Harga Grosir';
                break;

            case 'price_ecer_roll':
                $priceType = 'Harga Roll Ecer';
                break;

            case 'price_ecer':
                $priceType = 'Harga Ecer';
                break;
            default:
                $priceType = '-';
                break;
        }

        return $priceType;
    }
