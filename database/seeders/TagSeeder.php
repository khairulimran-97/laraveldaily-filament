<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tag::create(['name' => 'on sale', 'company_id' => Company::inRandomOrder()->value('id')]);
        Tag::create(['name' => 'new edition', 'company_id' => Company::inRandomOrder()->value('id')]);
        Tag::create(['name' => 'trending', 'company_id' => Company::inRandomOrder()->value('id')]);
    }
}
