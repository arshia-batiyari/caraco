<?php

namespace Database\Seeders;

use App\Models\Booth;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class BoothSeeder extends Seeder
{
    public function run(): void
    {
        $booths = [
            [
                'name' => 'هنر سنتی کاشان',
                'name_en' => 'Kashan Traditional Art',
                'slug' => 'honar-sonnati-kashan',
                'desc' => 'تولیدکننده ظروف مسی و قلمزنی اصیل کاشان با بیش از ۲۰ سال سابقه در صنایع دستی سنتی ایران.',
                'desc_en' => 'Maker of authentic Kashan copperware and engraved metalwork, with over 20 years of experience in traditional Iranian handicrafts.',
                'province' => 'اصفهان',
                'city' => 'کاشان',
                'categories' => ['handicrafts-metalwork'],
                'online' => true,
                'commission' => 10,
            ],
            [
                'name' => 'پرشین زمین البرز',
                'name_en' => 'Persian Zamin Alborz',
                'slug' => 'persian-zamin-alborz',
                'desc' => 'مشاور تخصصی زمین و ویلا در مناطق شمالی و غربی کشور.',
                'desc_en' => 'Specialist real-estate consultancy for land and villas in the northern and western regions of Iran.',
                'province' => 'البرز',
                'city' => 'کرج',
                'categories' => ['real-estate'],
                'online' => false,
                'commission' => 8,
            ],
            [
                'name' => 'فرش گل‌ابریشم',
                'name_en' => 'Gol-e Abrisham Carpets',
                'slug' => 'gol-abrisham-carpets',
                'desc' => 'فرش‌های دستباف تبریز و قشقایی، ارسال به سراسر کشور.',
                'desc_en' => 'Handwoven Tabriz and Qashqai carpets, shipped nationwide.',
                'province' => 'آذربایجان شرقی',
                'city' => 'تبریز',
                'categories' => ['handicrafts-rugs'],
                'online' => true,
                'commission' => 12,
            ],
            [
                'name' => 'نگین بافت یزد',
                'name_en' => 'Negin Bafte Yazd',
                'slug' => 'negin-bafte-yazd',
                'desc' => 'ترمه و پارچه‌های سنتی یزدی، تولید و عمده‌فروشی.',
                'desc_en' => 'Manufacturer and wholesaler of Yazd termeh and traditional textiles.',
                'province' => 'یزد',
                'city' => 'یزد',
                'categories' => ['handicrafts-textiles'],
                'online' => true,
                'commission' => 10,
            ],
            [
                'name' => 'مس فیروزه اصفهان',
                'name_en' => 'Mese Firouzeh Isfahan',
                'slug' => 'mese-firouzeh-isfahan',
                'desc' => 'ظروف مسی قلمزنی و میناکاری‌شده با طرح‌های اصفهانی اصیل.',
                'desc_en' => 'Engraved and enameled copperware with authentic Isfahan patterns.',
                'province' => 'اصفهان',
                'city' => 'اصفهان',
                'categories' => ['handicrafts-metalwork'],
                'online' => true,
                'commission' => 10,
            ],
            [
                'name' => 'چوب و مبل ملارد',
                'name_en' => 'Choobo Mobl Malard',
                'slug' => 'choobo-mobl-malard',
                'desc' => 'مبلمان چوبی سنتی و مدرن با چوب گردو و راش.',
                'desc_en' => 'Traditional and modern wooden furniture made from walnut and beech wood.',
                'province' => 'تهران',
                'city' => 'ملارد',
                'categories' => ['handicrafts-furniture'],
                'online' => false,
                'commission' => 9,
            ],
            [
                'name' => 'سفال گلپایگان',
                'name_en' => 'Sofal Golpayegan',
                'slug' => 'sofal-golpayegan',
                'desc' => 'ظروف سفالی دست‌ساز با نقاشی سنتی اصفهانی.',
                'desc_en' => 'Handmade pottery decorated with traditional Isfahan-style painting.',
                'province' => 'اصفهان',
                'city' => 'گلپایگان',
                'categories' => ['handicrafts-pottery'],
                'online' => false,
                'commission' => 10,
            ],
            [
                'name' => 'باغ سبز شمال',
                'name_en' => 'Bagh Sabz Shomal',
                'slug' => 'bagh-sabz-shomal',
                'desc' => 'فروش مستقیم محصولات ارگانیک و صیفی‌جات تازه از مزارع گیلان.',
                'desc_en' => 'Direct sales of organic produce and fresh vegetables from Gilan farms.',
                'province' => 'گیلان',
                'city' => 'رشت',
                'categories' => ['agriculture'],
                'online' => true,
                'commission' => 7,
            ],
            [
                'name' => 'سیمان سپهر ایرانیان',
                'name_en' => 'Sepehr Cement Co.',
                'slug' => 'sepehr-cement-iranian',
                'desc' => 'عرضه‌کننده انواع سیمان تیپ ۲، تیپ ۵ و سیمان سفید برای پروژه‌های ساختمانی و عمرانی.',
                'desc_en' => 'Supplier of Type II, Type V and white cement for residential and civil engineering projects.',
                'province' => 'تهران',
                'city' => 'تهران',
                'categories' => ['building-cement'],
                'online' => true,
                'commission' => 6,
            ],
            [
                'name' => 'آجر و بلوک برتر',
                'name_en' => 'Bartar Brick & Block',
                'slug' => 'bartar-brick-block',
                'desc' => 'تولید و پخش آجر سفالی، بلوک سبک و مصالح دیوارچینی برای پروژه‌های انبوه‌سازی.',
                'desc_en' => 'Manufacturer and distributor of clay bricks, lightweight blocks and masonry supplies.',
                'province' => 'قم',
                'city' => 'قم',
                'categories' => ['building-brick'],
                'online' => true,
                'commission' => 6,
            ],
            [
                'name' => 'سنگ و سرامیک پارس',
                'name_en' => 'Pars Stone & Tile',
                'slug' => 'pars-stone-tile',
                'desc' => 'تامین‌کننده سنگ ساختمانی، سرامیک پرسلانی و کاشی‌های دکوراتیو برای نما و داخلی.',
                'desc_en' => 'Supplier of building stone, porcelain tiles and decorative ceramics for facade and interior use.',
                'province' => 'اصفهان',
                'city' => 'نجف‌آباد',
                'categories' => ['building-stone'],
                'online' => true,
                'commission' => 6,
            ],
            [
                'name' => 'تاسیسات آب‌گستر',
                'name_en' => 'Abgostar Installations',
                'slug' => 'abgostar-installations',
                'desc' => 'ارائه تجهیزات لوله‌کشی، اتصالات، شیرآلات و سیستم‌های گرمایشی برای ساختمان.',
                'desc_en' => 'Provider of plumbing equipment, fittings, valves and heating systems for buildings.',
                'province' => 'خراسان رضوی',
                'city' => 'مشهد',
                'categories' => ['building-installations'],
                'online' => true,
                'commission' => 6,
            ],
        ];

        foreach ($booths as $i => $data) {
            $email = 'vendor'.($i + 1).'@meydan.test';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'].' (غرفه‌دار)',
                    'phone' => '0912'.str_pad((string) (1000000 + $i), 7, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'role' => 'vendor',
                ]
            );

            $booth = Booth::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => ['fa' => $data['name'], 'en' => $data['name_en']],
                    'slug' => $data['slug'],
                    'description' => ['fa' => $data['desc'], 'en' => $data['desc_en']],
                    'province' => $data['province'],
                    'city' => $data['city'],
                    'phone' => '021'.str_pad((string) (10000000 + $i), 8, '0', STR_PAD_LEFT),
                    'status' => 'approved',
                    'approved_at' => now()->subDays(random_int(5, 200)),
                    'online_payment_enabled' => $data['online'],
                    'commission_percent' => $data['commission'],
                    'bank_account_holder' => $data['name'].' (نمونه)',
                    'bank_name' => 'بانک ملت',
                    'bank_iban' => 'IR'.str_pad((string) (820540100000 + $i), 24, '0', STR_PAD_RIGHT),
                ]
            );

            $categoryIds = Category::whereIn('slug', $data['categories'])->pluck('id');
            $booth->categories()->sync($categoryIds);

            if (! $booth->banner) {
                $this->attachDemoBanner($booth, $data['categories'][0] ?? 'default');
            }
        }
    }

    private function attachDemoBanner(Booth $booth, string $categorySlug): void
    {
        $sourceSlug = $this->resolveDemoSlug($categorySlug);
        $source = resource_path("demo-images/banners/{$sourceSlug}.jpg");

        if (! is_file($source)) {
            $source = resource_path('demo-images/banners/default.jpg');
        }
        if (! is_file($source)) {
            return;
        }

        $filename = 'demo-'.$booth->id.'-'.basename($source);
        $relativePath = 'booths/'.$filename;
        $contents = file_get_contents($source);

        Storage::disk('public')->put($relativePath, $contents);

        File::ensureDirectoryExists(public_path('storage/booths'));
        File::put(public_path('storage/'.$relativePath), $contents);

        $booth->update(['banner' => $relativePath]);
    }

    private function resolveDemoSlug(string $categorySlug): string
    {
        return match (true) {
            str_starts_with($categorySlug, 'real-estate') => 'real-estate',
            default => $categorySlug,
        };
    }
}
