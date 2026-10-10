<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'icon' => '🏗️', 'fa' => 'مصالح ساختمانی', 'en' => 'Building Materials', 'slug' => 'building-materials',
                'children' => [
                    ['fa' => 'سیمان', 'en' => 'Cement', 'slug' => 'building-cement'],
                    ['fa' => 'آجر و بلوک', 'en' => 'Brick & Block', 'slug' => 'building-brick'],
                    ['fa' => 'سنگ و سرامیک', 'en' => 'Stone & Tile', 'slug' => 'building-stone'],
                    ['fa' => 'تاسیسات', 'en' => 'Installations', 'slug' => 'building-installations'],
                ],
            ],
            [
                'icon' => '🏠', 'fa' => 'املاک و ساخت‌وساز', 'en' => 'Real Estate & Construction', 'slug' => 'real-estate',
                'children' => [
                    ['fa' => 'زمین', 'en' => 'Land', 'slug' => 'real-estate-land'],
                    ['fa' => 'ویلا', 'en' => 'Villa', 'slug' => 'real-estate-villa'],
                    ['fa' => 'آپارتمان', 'en' => 'Apartment', 'slug' => 'real-estate-apartment'],
                ],
            ],
            [
                'icon' => '🎨', 'fa' => 'صنایع دستی', 'en' => 'Handicrafts', 'slug' => 'handicrafts',
                'children' => [
                    ['fa' => 'مبلمان', 'en' => 'Furniture', 'slug' => 'handicrafts-furniture'],
                    ['fa' => 'فرش و گلیم', 'en' => 'Rugs & Kilims', 'slug' => 'handicrafts-rugs'],
                    ['fa' => 'ظروف', 'en' => 'Dishware', 'slug' => 'handicrafts-dishware'],
                    ['fa' => 'قلمزنی و فلزکاری', 'en' => 'Metalwork', 'slug' => 'handicrafts-metalwork'],
                    ['fa' => 'سفالگری', 'en' => 'Pottery', 'slug' => 'handicrafts-pottery'],
                    ['fa' => 'پارچه و بافتنی', 'en' => 'Textiles', 'slug' => 'handicrafts-textiles'],
                ],
            ],
            [
                'icon' => '🚗', 'fa' => 'خودرو', 'en' => 'Automotive', 'slug' => 'automotive',
                'children' => [
                    ['fa' => 'خودرو سواری', 'en' => 'Cars', 'slug' => 'automotive-cars'],
                    ['fa' => 'لوازم یدکی', 'en' => 'Auto Parts', 'slug' => 'automotive-parts'],
                ],
            ],
            [
                'icon' => '👕', 'fa' => 'پوشاک', 'en' => 'Clothing', 'slug' => 'clothing',
                'children' => [
                    ['fa' => 'پوشاک زنانه', 'en' => "Women's Wear", 'slug' => 'clothing-women'],
                    ['fa' => 'پوشاک مردانه', 'en' => "Men's Wear", 'slug' => 'clothing-men'],
                ],
            ],
            [
                'icon' => '🌾', 'fa' => 'کشاورزی و محصولات ارگانیک', 'en' => 'Agriculture & Organic', 'slug' => 'agriculture',
                'children' => [],
            ],
            [
                'icon' => '🍽️', 'fa' => 'مواد غذایی', 'en' => 'Groceries & Food', 'slug' => 'food',
                'children' => [
                    ['fa' => 'خشکبار و آجیل', 'en' => 'Dried Fruits & Nuts', 'slug' => 'food-dried'],
                    ['fa' => 'لبنیات', 'en' => 'Dairy', 'slug' => 'food-dairy'],
                    ['fa' => 'کنسرو و غذای آماده', 'en' => 'Canned & Ready Food', 'slug' => 'food-canned'],
                    ['fa' => 'ادویه‌جات و چاشنی', 'en' => 'Spices & Condiments', 'slug' => 'food-spices'],
                ],
            ],
            [
                'icon' => '💻', 'fa' => 'الکترونیک', 'en' => 'Electronics', 'slug' => 'electronics',
                'children' => [],
            ],
        ];

        foreach ($tree as $i => $root) {
            $parent = Category::firstOrCreate(
                ['slug' => $root['slug']],
                [
                    'name' => ['fa' => $root['fa'], 'en' => $root['en']],
                    'icon' => $root['icon'],
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );

            foreach ($root['children'] as $j => $child) {
                Category::firstOrCreate(
                    ['slug' => $child['slug']],
                    [
                        'parent_id' => $parent->id,
                        'name' => ['fa' => $child['fa'], 'en' => $child['en']],
                        'sort_order' => $j,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
