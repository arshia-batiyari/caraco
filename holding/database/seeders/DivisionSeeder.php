<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['tourism', 1, 'tourism', [
                'fa' => 'آژانس گردشگری و تبلیغات',
                'en' => 'Tourism and advertising agency',
                'ru' => 'Туристическое и рекламное агентство',
                'ar' => 'وكالة السياحة والإعلان',
            ]],
            ['real-estate', 2, 'real-estate', [
                'fa' => 'املاک و مستغلات',
                'en' => 'Real estate',
                'ru' => 'Недвижимость',
                'ar' => 'العقارات',
            ]],
            ['commercial', 3, 'commercial-services', [
                'fa' => 'خدمات بازرگانی',
                'en' => 'Commercial services',
                'ru' => 'Коммерческие услуги',
                'ar' => 'الخدمات التجارية',
            ]],
            ['agriculture', 4, 'agriculture', [
                'fa' => 'کشاورزی',
                'en' => 'Agriculture',
                'ru' => 'Сельское хозяйство',
                'ar' => 'الزراعة',
            ]],
            ['engineering', 5, 'engineering', [
                'fa' => 'خدمات فنی و مهندسی',
                'en' => 'Technical and engineering services',
                'ru' => 'Технические и инженерные услуги',
                'ar' => 'الخدمات الفنية والهندسية',
            ]],
            ['legal', 6, 'legal-services', [
                'fa' => 'خدمات حقوقی و اداری',
                'en' => 'Legal and administrative services',
                'ru' => 'Юридические и административные услуги',
                'ar' => 'الخدمات القانونية والإدارية',
            ]],
            ['trading', 7, 'trading', [
                'fa' => 'بازرگانی و تجارت عمومی',
                'en' => 'General commerce and trading',
                'ru' => 'Общая торговля',
                'ar' => 'التجارة العامة',
            ]],
            ['construction', 8, 'construction', [
                'fa' => 'ساخت‌وساز و ساختمان',
                'en' => 'Construction and building',
                'ru' => 'Строительство',
                'ar' => 'البناء والتشييد',
            ]],
            ['social', 9, 'social', [
                'fa' => 'فعالیت‌های اجتماعی و نیکوکاری',
                'en' => 'Social activities and charitable initiatives',
                'ru' => 'Социальные и благотворительные инициативы',
                'ar' => 'الأنشطة الاجتماعية والخيرية',
            ]],
        ];

        foreach ($divisions as [$key, $position, $slug, $titles]) {
            $division = Division::query()->updateOrCreate(
                ['key' => $key],
                ['position' => $position, 'is_published' => true]
            );

            foreach ($titles as $locale => $title) {
                $division->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title' => $title,
                        'slug' => $slug,
                        'introduction' => null,
                        'description' => null,
                    ]
                );
            }
        }
    }
}
