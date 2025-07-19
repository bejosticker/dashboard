<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Stiker Transparan A4', 'Stiker Vinyl Glossy', 'Stiker Cromo Doff',
            'Stiker Hologram Silver', 'Stiker Kertas Kraft', 'Stiker Transparan Roll',
            'Stiker Vinyl Doff', 'Stiker PVC Waterproof', 'Stiker Label Nama',
            'Stiker Cutting Warna Merah', 'Stiker Cutting Warna Hitam',
            'Stiker Glossy A3', 'Stiker Label Makanan', 'Stiker Barcode Thermal',
            'Stiker Fragile', 'Stiker Void', 'Stiker Segel Botol', 'Stiker Oval Label',
            'Stiker Label Minyak', 'Stiker Produk Kecantikan', 'Stiker Label Herbal',
            'Stiker Cromo 70gsm', 'Stiker Gold Foil', 'Stiker UV Laminasi',
            'Stiker Label Sambal', 'Stiker Custom Logo', 'Stiker Doff Roll',
            'Stiker Label Kue', 'Stiker Label Susu', 'Stiker Label Parfum',
            'Stiker Bening Roll', 'Stiker Roll 10 Meter', 'Stiker Glossy Waterproof',
            'Stiker Cromo A4', 'Stiker Doff A4', 'Stiker HVS Label',
            'Stiker Kue Ulang Tahun', 'Stiker Nama Anak', 'Stiker Thank You',
            'Stiker Label Alamat', 'Stiker Label Botol', 'Stiker Label Sambal Pedas',
            'Stiker Glossy Inkjet', 'Stiker Doff Inkjet', 'Stiker Botol Parfum',
            'Stiker Makanan Ringan', 'Stiker Barcode QR', 'Stiker Segel Garansi',
            'Stiker Tahan Air', 'Stiker Label Minuman'
        ];

        $products = [];

        foreach ($names as $name) {
            $products[] = [
                'name' => $name,
                'image' => 'default.png',
                'price_agent' => rand(8000, 15000),
                'price_grosir' => rand(10000, 17000),
                'price_ecer_roll' => rand(11000, 18000),
                'price_ecer' => rand(12000, 20000),
                'price_kulak' => rand(7000, 12000),
                'stock_cm' => rand(500, 10000),
                'per_roll_cm' => 1000,
                'minimum_stock_cm' => rand(500, 2000),
            ];
        }

        DB::table('products')->insert($products);
    }
}
