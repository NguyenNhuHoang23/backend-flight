<?php

namespace Database\Seeders;

use App\Models\AirlineDiscount;
use Illuminate\Database\Seeder;

class AirlineDiscountSeeder extends Seeder
{
    public function run(): void
    {
        $airlines = [
            ['airline_code' => 'BAMBOO', 'airline_name' => 'Bamboo Airways', 'discount_rate' => 30, 'is_custom_enabled' => true],
            ['airline_code' => 'VIETJET', 'airline_name' => 'VietJet Air', 'discount_rate' => 30, 'is_custom_enabled' => true],
            ['airline_code' => 'VNA', 'airline_name' => 'Vietnam Airlines', 'discount_rate' => 30, 'is_custom_enabled' => true],
            ['airline_code' => 'VIETRAVEL', 'airline_name' => 'Vietravel Airlines', 'discount_rate' => 20, 'is_custom_enabled' => true],
            ['airline_code' => 'GALILEO', 'airline_name' => 'Galileo (GDS)', 'discount_rate' => 10, 'is_custom_enabled' => false],
            ['airline_code' => 'JETSTAR', 'airline_name' => 'Jetstar Pacific', 'discount_rate' => 10, 'is_custom_enabled' => false],
            ['airline_code' => 'SABRE', 'airline_name' => 'Sabre (GDS)', 'discount_rate' => 10, 'is_custom_enabled' => false],
            ['airline_code' => 'SPA', 'airline_name' => 'SPA Airlines', 'discount_rate' => 10, 'is_custom_enabled' => false],
        ];

        foreach ($airlines as $airline) {
            AirlineDiscount::updateOrCreate(
                ['airline_code' => $airline['airline_code']],
                $airline
            );
        }
    }
}
