<?php

namespace Database\Seeders;

use App\Models\Booth;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'honar-sonnati-kashan' => [
                ['name_fa' => 'سینی مسی قلمزنی طرح گل و مرغ', 'name_en' => 'Engraved Copper Tray — Bird & Flower', 'price' => 1850000, 'stock' => 12, 'weight' => 900, 'discount' => 12],
                ['name_fa' => 'گلدان میناکاری اصفهان', 'name_en' => 'Isfahan Enamel Vase', 'price' => 920000, 'stock' => 8, 'weight' => 700, 'discount' => 8],
                ['name_fa' => 'جعبه جواهرات نقره‌کوب', 'name_en' => 'Silver-Inlaid Jewelry Box', 'price' => 2400000, 'stock' => 2, 'weight' => 400, 'discount' => 0],
                ['name_fa' => 'تندیس مسی طرح فیل', 'name_en' => 'Copper Elephant Statuette', 'price' => 680000, 'stock' => 15, 'weight' => 1200, 'discount' => 0],
                ['name_fa' => 'سرویس چای‌خوری قلمزنی ۶ نفره', 'name_en' => 'Engraved 6-Piece Tea Set', 'price' => 3200000, 'stock' => 5, 'weight' => 2500, 'discount' => 15],
                ['name_fa' => 'آینه قاب مسی طرح سنتی', 'name_en' => 'Traditional Copper-Framed Mirror', 'price' => 1450000, 'stock' => 4, 'weight' => 1800, 'discount' => 10],
            ],
            'persian-zamin-alborz' => [
                ['name_fa' => 'زمین ۵۰۰ متری کوهستانی کرج', 'name_en' => '500 sqm Mountainside Land, Karaj', 'price' => 850000000, 'stock' => 1, 'weight' => 500, 'discount' => 0, 'category_slug' => 'real-estate-land'],
                ['name_fa' => 'ویلای دوبلکس ۲۲۰ متری استخردار', 'name_en' => '220 sqm Duplex Villa with Pool', 'price' => 4200000000, 'stock' => 1, 'weight' => 500, 'discount' => 0, 'category_slug' => 'real-estate-villa'],
                ['name_fa' => 'زمین کشاورزی ۲۰۰۰ متری', 'name_en' => '2,000 sqm Agricultural Land', 'price' => 1200000000, 'stock' => 1, 'weight' => 500, 'discount' => 0, 'category_slug' => 'real-estate-land'],
            ],
            'gol-abrisham-carpets' => [
                ['name_fa' => 'فرش دستباف تبریز ۶ متری', 'name_en' => 'Handwoven Tabriz Rug, 6 sqm', 'price' => 28000000, 'stock' => 3, 'weight' => 12000, 'discount' => 6],
                ['name_fa' => 'گلیم قشقایی ۲×۳', 'name_en' => 'Qashqai Kilim, 2×3m', 'price' => 4200000, 'stock' => 7, 'weight' => 3500, 'discount' => 0],
                ['name_fa' => 'قالیچه ابریشم طرح افشان', 'name_en' => 'Silk Rug — Afshan Pattern', 'price' => 15500000, 'stock' => 2, 'weight' => 1500, 'discount' => 9],
                ['name_fa' => 'فرش ماشینی طرح کلاسیک ۹ متری', 'name_en' => 'Machine-Woven Classic Rug, 9 sqm', 'price' => 6800000, 'stock' => 20, 'weight' => 9000, 'discount' => 5],
            ],
            'negin-bafte-yazd' => [
                ['name_fa' => 'ترمه یزد طرح سنتی (۱ متر)', 'name_en' => 'Yazd Termeh Fabric — Traditional Pattern (1m)', 'price' => 650000, 'stock' => 30, 'weight' => 300, 'discount' => 0],
                ['name_fa' => 'رومیزی ترمه دست‌باف', 'name_en' => 'Handwoven Termeh Tablecloth', 'price' => 1200000, 'stock' => 14, 'weight' => 500, 'discount' => 13],
                ['name_fa' => 'شال ترمه ابریشم', 'name_en' => 'Silk Termeh Shawl', 'price' => 980000, 'stock' => 22, 'weight' => 200, 'discount' => 7],
            ],
            'mese-firouzeh-isfahan' => [
                ['name_fa' => 'سرویس قلیان مسی میناکاری', 'name_en' => 'Enameled Copper Hookah Set', 'price' => 2100000, 'stock' => 6, 'weight' => 2200, 'discount' => 11],
                ['name_fa' => 'ظرف میوه‌خوری مسی بزرگ', 'name_en' => 'Large Copper Fruit Bowl', 'price' => 1350000, 'stock' => 9, 'weight' => 1600, 'discount' => 0],
                ['name_fa' => 'گلدان میناکاری کوچک', 'name_en' => 'Small Enamel Vase', 'price' => 540000, 'stock' => 18, 'weight' => 500, 'discount' => 6],
            ],
            'choobo-mobl-malard' => [
                ['name_fa' => 'مبل راحتی ۷ نفره چوب گردو', 'name_en' => '7-Seat Walnut Wood Sofa Set', 'price' => 45000000, 'stock' => 2, 'weight' => 85000, 'discount' => 10],
                ['name_fa' => 'میز جلومبلی منبت‌کاری', 'name_en' => 'Hand-Carved Coffee Table', 'price' => 8200000, 'stock' => 4, 'weight' => 22000, 'discount' => 0],
                ['name_fa' => 'کتابخانه چوبی سه‌طبقه', 'name_en' => '3-Shelf Wooden Bookcase', 'price' => 6500000, 'stock' => 6, 'weight' => 35000, 'discount' => 5],
            ],
            'sofal-golpayegan' => [
                ['name_fa' => 'کاسه سفالی نقاشی‌شده', 'name_en' => 'Hand-Painted Ceramic Bowl', 'price' => 320000, 'stock' => 25, 'weight' => 600, 'discount' => 0],
                ['name_fa' => 'گلدان سفالی بزرگ حیاطی', 'name_en' => 'Large Ceramic Garden Pot', 'price' => 890000, 'stock' => 10, 'weight' => 4500, 'discount' => 8],
                ['name_fa' => 'سرویس چینی سفالی ۱۲ پارچه', 'name_en' => '12-Piece Ceramic Dinnerware Set', 'price' => 2650000, 'stock' => 3, 'weight' => 3800, 'discount' => 12],
            ],
            'bagh-sabz-shomal' => [
                ['name_fa' => 'برنج طارم هاشمی (۱۰ کیلوگرم)', 'name_en' => 'Hashemi Tarom Rice (10kg)', 'price' => 1450000, 'stock' => 40, 'weight' => 10000, 'discount' => 7],
                ['name_fa' => 'عسل طبیعی کوهستان (۱ کیلوگرم)', 'name_en' => 'Natural Mountain Honey (1kg)', 'price' => 780000, 'stock' => 35, 'weight' => 1100, 'discount' => 0],
                ['name_fa' => 'زیتون پرورده گیلان (۱ کیلوگرم)', 'name_en' => 'Gilan Marinated Olives (1kg)', 'price' => 320000, 'stock' => 50, 'weight' => 1050, 'discount' => 5],
            ],
            'sepehr-cement-iranian' => [
                ['name_fa' => 'سیمان تیپ ۲ پاکتی ۵۰ کیلویی', 'name_en' => 'Type II Cement — 50kg Bag', 'price' => 285000, 'stock' => 120, 'weight' => 50000, 'discount' => 4, 'category_slug' => 'building-cement'],
                ['name_fa' => 'سیمان سفید نما ۴۰ کیلویی', 'name_en' => 'White Cement for Facade — 40kg', 'price' => 338000, 'stock' => 90, 'weight' => 40000, 'discount' => 0, 'category_slug' => 'building-cement'],
                ['name_fa' => 'سیمان تیپ ۵ ضدسولفات', 'name_en' => 'Type V Sulfate-Resistant Cement', 'price' => 312000, 'stock' => 65, 'weight' => 50000, 'discount' => 6, 'category_slug' => 'building-cement'],
                ['name_fa' => 'چسب بتن پلیمری ۲۰ کیلویی', 'name_en' => 'Polymer Concrete Adhesive — 20kg', 'price' => 465000, 'stock' => 32, 'weight' => 20000, 'discount' => 10, 'category_slug' => 'building-cement'],
            ],
            'bartar-brick-block' => [
                ['name_fa' => 'آجر سفالی تیغه ۱۰ سانتی', 'name_en' => '10cm Hollow Clay Brick', 'price' => 9800, 'stock' => 5000, 'weight' => 2200, 'discount' => 0, 'category_slug' => 'building-brick'],
                ['name_fa' => 'بلوک سبک AAC سایز ۲۰', 'name_en' => 'AAC Lightweight Block — Size 20', 'price' => 86000, 'stock' => 850, 'weight' => 14000, 'discount' => 7, 'category_slug' => 'building-brick'],
                ['name_fa' => 'آجر نسوز نما شاموتی', 'name_en' => 'Firebrick Facade — Chamotte', 'price' => 24500, 'stock' => 2800, 'weight' => 2600, 'discount' => 9, 'category_slug' => 'building-brick'],
                ['name_fa' => 'بلوک سیمانی سنگین ۲۰×۲۰×۴۰', 'name_en' => 'Heavy Concrete Block 20×20×40', 'price' => 69500, 'stock' => 1200, 'weight' => 17000, 'discount' => 0, 'category_slug' => 'building-brick'],
            ],
            'pars-stone-tile' => [
                ['name_fa' => 'سنگ تراورتن عباس‌آباد ممتاز', 'name_en' => 'Premium Abbasabad Travertine', 'price' => 1280000, 'stock' => 180, 'weight' => 30000, 'discount' => 8, 'category_slug' => 'building-stone'],
                ['name_fa' => 'سرامیک پرسلانی ۶۰×۶۰ مات', 'name_en' => 'Porcelain Tile 60×60 Matte', 'price' => 685000, 'stock' => 240, 'weight' => 25000, 'discount' => 0, 'category_slug' => 'building-stone'],
                ['name_fa' => 'کاشی بین‌کابینتی طرح مرمر', 'name_en' => 'Backsplash Marble-Pattern Tile', 'price' => 398000, 'stock' => 120, 'weight' => 18000, 'discount' => 12, 'category_slug' => 'building-stone'],
                ['name_fa' => 'سنگ اسلب چینی الیگودرز', 'name_en' => 'Aligudarz Crystal Slab Stone', 'price' => 2450000, 'stock' => 60, 'weight' => 50000, 'discount' => 5, 'category_slug' => 'building-stone'],
            ],
            'abgostar-installations' => [
                ['name_fa' => 'لوله پنج‌لایه ۲۰ میلی‌متر', 'name_en' => '20mm Five-Layer Pipe', 'price' => 980000, 'stock' => 70, 'weight' => 15000, 'discount' => 0, 'category_slug' => 'building-installations'],
                ['name_fa' => 'شیرآلات روشویی کروم', 'name_en' => 'Chrome Basin Faucet', 'price' => 1850000, 'stock' => 26, 'weight' => 2400, 'discount' => 11, 'category_slug' => 'building-installations'],
                ['name_fa' => 'پکیج دیواری ۲۴۰۰۰', 'name_en' => 'Wall-Mounted Boiler 24000', 'price' => 17900000, 'stock' => 12, 'weight' => 32000, 'discount' => 6, 'category_slug' => 'building-installations'],
                ['name_fa' => 'کلکتور گرمایش از کف ۸ مدار', 'name_en' => 'Underfloor Heating Manifold — 8 Loops', 'price' => 3650000, 'stock' => 18, 'weight' => 5200, 'discount' => 9, 'category_slug' => 'building-installations'],
            ],
        ];

        $descriptions = [
            'fa' => 'محصولی باکیفیت و تستی برای بررسی کامل ویترین، جستجو، سبد خرید و صفحه محصول در پروژه هاچ لند.',
            'en' => 'A high-quality demo product used to test catalog display, product pages, search and checkout flows in Haach Land.',
        ];

        foreach ($catalog as $boothSlug => $products) {
            $booth = Booth::with('categories')->where('slug', $boothSlug)->first();
            if (! $booth) {
                continue;
            }

            foreach ($products as $p) {
                $category = isset($p['category_slug'])
                    ? Category::where('slug', $p['category_slug'])->first()
                    : ($booth->categories->first() ?? Category::first());

                if (! $category) {
                    $category = Category::first();
                }

                $sku = Str::slug($boothSlug.'-'.$p['name_en']);

                $product = Product::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'booth_id' => $booth->id,
                        'category_id' => $category->id,
                        'name' => ['fa' => $p['name_fa'], 'en' => $p['name_en']],
                        'description' => [
                            'fa' => $descriptions['fa'].' محصول از غرفه '.$booth->localized_name.' ارائه شده است.',
                            'en' => $descriptions['en'].' Offered by '.$booth->localized_name.'.',
                        ],
                        'price' => $p['price'],
                        'discount_percent' => $p['discount'] ?? 0,
                        'stock' => $p['stock'],
                        'weight_grams' => max(500, (int) $p['weight']),
                        'status' => 'active',
                    ]
                );

                if ($product->images()->count() === 0) {
                    $this->attachDemoImage($product, $category->slug);
                }
            }
        }
    }

    private function attachDemoImage(Product $product, string $categorySlug): void
    {
        $sourceSlug = $this->resolveDemoSlug($categorySlug);
        $source = resource_path("demo-images/products/{$sourceSlug}.jpg");

        if (! is_file($source)) {
            $source = resource_path('demo-images/products/default.jpg');
        }
        if (! is_file($source)) {
            return;
        }

        $filename = 'demo-'.$product->id.'-'.basename($source);
        $relativePath = 'products/'.$filename;
        $contents = file_get_contents($source);

        Storage::disk('public')->put($relativePath, $contents);

        File::ensureDirectoryExists(public_path('storage/products'));
        File::put(public_path('storage/'.$relativePath), $contents);

        $product->images()->create([
            'path' => $relativePath,
            'sort_order' => 0,
        ]);
    }

    private function resolveDemoSlug(string $categorySlug): string
    {
        return match (true) {
            str_starts_with($categorySlug, 'real-estate') => 'real-estate',
            default => $categorySlug,
        };
    }
}
