<?php

namespace Database\Seeders;

use App\Models\NavigationItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        Page::query()->updateOrCreate(
            ['key' => 'about'],
            ['is_published' => false, 'position' => 1]
        );

        if (NavigationItem::query()->exists()) {
            return;
        }

        $header = [
            ['home', 1],
            ['about', 2],
            ['divisions.index', 3],
            ['projects.index', 4],
            ['contact', 5],
        ];
        $footer = [
            ['about', 1],
            ['divisions.index', 2],
            ['contact', 3],
        ];

        foreach (['header' => $header, 'footer' => $footer] as $location => $items) {
            foreach ($items as [$route, $position]) {
                NavigationItem::query()->create([
                    'location' => $location,
                    'kind' => 'route',
                    'route_name' => $route,
                    'position' => $position,
                    'is_published' => true,
                ]);
            }
        }
    }
}
