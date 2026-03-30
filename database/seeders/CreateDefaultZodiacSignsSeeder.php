<?php

namespace Database\Seeders;

use App\Models\ZodiacSign;
use Illuminate\Database\Seeder;

class CreateDefaultZodiacSignsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zodiacs = [
            ['name' => 'Aries', 'slug' => 'aries'],
            ['name' => 'Taurus', 'slug' => 'taurus'],
            ['name' => 'Gemini', 'slug' => 'gemini'],
            ['name' => 'Cancer', 'slug' => 'cancer'],
            ['name' => 'Leo', 'slug' => 'leo'],
            ['name' => 'Virgo', 'slug' => 'virgo'],
            ['name' => 'Libra', 'slug' => 'libra'],
            ['name' => 'Scorpio', 'slug' => 'scorpio'],
            ['name' => 'Sagittarius', 'slug' => 'sagittarius'],
            ['name' => 'Capricorn', 'slug' => 'capricorn'],
            ['name' => 'Aquarius', 'slug' => 'aquarius'],
            ['name' => 'Pisces', 'slug' => 'pisces'],
        ];

        foreach ($zodiacs as $zodiac) {
            ZodiacSign::updateOrCreate(
                ['slug' => $zodiac['slug']],
                [
                    'name' => $zodiac['name'],
                    'status' => 1,
                ]
            );
        }
    }
}
