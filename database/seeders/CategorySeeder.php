<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Gündem', 'slug' => 'gundem'],
            ['name' => 'Dünya', 'slug' => 'dunya'],
            ['name' => 'Ekonomi', 'slug' => 'ekonomi'],
            ['name' => 'Spor', 'slug' => 'spor'],
            ['name' => 'Teknoloji', 'slug' => 'teknoloji'],
            ['name' => 'Kültür', 'slug' => 'kultur'],
        ];

        foreach ($rows as $row) {
            Category::updateOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']]
            );
        }
    }
}
