<?php

    function formatRupiah($angka, $prefix = 'Rp')
    {
        return $prefix . ' ' . number_format($angka, 0, ',', '.');
    }
