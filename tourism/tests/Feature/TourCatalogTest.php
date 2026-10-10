<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Inquiry;
use App\Models\RoutePoint;
use App\Models\Tour;
use App\Models\User;
use Tests\TestCase;

class TourCatalogTest extends TestCase
{
    public function test_guest_is_sent_to_the_tourism_login_and_editors_cannot_manage_users(): void
    {
        $this->get('/admin/tours')->assertRedirect(route('admin.login'));

        $editor = User::factory()->create();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
    }

    public function test_an_editor_can_publish_a_tour_and_the_public_pages_show_only_entered_facts(): void
    {
        $editor = User::factory()->create();
        $destination = Destination::query()->create([
            'type' => 'city',
            'is_published' => true,
            'is_featured' => true,
            'position' => 1,
        ]);
        $destination->translations()->create([
            'locale' => 'en',
            'name' => 'Yazd',
            'slug' => 'yazd',
        ]);

        $this->actingAs($editor)->post('/admin/tours', [
            'scope' => 'domestic',
            'destination_id' => $destination->id,
            'is_published' => '1',
            'is_featured' => '1',
            'position' => 1,
            'currency' => 'eur',
            'base_price' => '200.00',
            'discount_percent' => '10',
            'duration_days' => 3,
            'duration_nights' => 2,
            'translations' => [
                'en' => [
                    'title' => 'Yazd lanes',
                    'summary' => 'Three days entered for the test.',
                    'departure_location' => 'Yazd coach station',
                    'terms' => 'Cancel seven days before departure.',
                ],
            ],
            'departures' => [
                ['departure_date' => '2026-11-02', 'return_date' => '2026-11-04', 'is_published' => '1'],
            ],
            'itinerary' => [
                ['day_number' => 1, 'translations' => ['en' => ['title' => 'Old city', 'body' => 'Walk the recorded lanes.']]],
            ],
            'points' => [
                ['latitude' => '31.8974', 'longitude' => '54.3569', 'day_number' => 1, 'translations' => ['en' => ['name' => 'Amir Chakhmaq']]],
                ['latitude' => '31.9000', 'longitude' => '54.3700', 'translations' => ['en' => ['name' => 'Dolatabad']]],
            ],
        ])->assertRedirect();

        $tour = Tour::query()->firstOrFail();
        $this->get('/en/tours')
            ->assertOk()
            ->assertSee('Yazd lanes')
            ->assertSee('10% less')
            ->assertSee('180.00 EUR')
            ->assertSee('200.00 EUR');

        $this->get('/en/tours/yazd-lanes')
            ->assertOk()
            ->assertSee('Yazd lanes')
            ->assertSee('Yazd coach station')
            ->assertSee('Day 1')
            ->assertSee('Walk the recorded lanes.')
            ->assertSee('Amir Chakhmaq')
            ->assertSee('Cancel seven days before departure.')
            ->assertSee('k-route-svg', false)
            ->assertSee('Ask about this tour')
            ->assertDontSee('aggregateRating')
            ->assertDontSee('InStock');

        $this->assertNotNull(RoutePoint::query()->where('tour_id', $tour->id)->whereNotNull('latitude')->first());
        $this->get('/en')->assertOk()->assertSee('Yazd lanes')->assertSee('Yazd');
    }

    public function test_discount_price_and_percent_cannot_be_combined_and_dates_must_match_duration(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->from('/admin/tours/create')->post('/admin/tours', [
            'scope' => 'international',
            'currency' => 'EUR',
            'base_price' => '100',
            'discount_price' => '80',
            'discount_percent' => '10',
            'translations' => ['en' => ['title' => 'Broken discount']],
        ])->assertRedirect('/admin/tours/create')->assertSessionHasErrors('discount_percent');

        $this->actingAs($editor)->from('/admin/tours/create')->post('/admin/tours', [
            'scope' => 'domestic',
            'currency' => 'EUR',
            'base_price' => '100',
            'duration_days' => 3,
            'duration_nights' => 3,
            'translations' => ['en' => ['title' => 'Broken nights']],
            'departures' => [
                ['departure_date' => '2026-11-02', 'return_date' => '2026-11-04', 'is_published' => '1'],
            ],
        ])->assertSessionHasErrors('duration_nights');

        $this->assertSame(0, Tour::query()->count());
    }

    public function test_price_filters_stay_inside_one_currency_and_filters_are_not_indexed(): void
    {
        $eur = $this->tour('Euro walk', 'EUR', '120.00');
        $this->tour('Rial walk', 'IRR', '120.00');

        $response = $this->get('/en/tours?currency=EUR&price_min=100&price_max=150');
        $response->assertOk()
            ->assertSee('Euro walk')
            ->assertDontSee('Rial walk')
            ->assertSee('noindex, follow', false)
            ->assertSee('name="currency" value="EUR"', false);

        $this->get('/en/tours?scope=spaceship')->assertRedirect('/en/tours');

        Tour::query()->whereKey($eur->id)->delete();
        foreach (range(1, 10) as $number) {
            $this->tour('Domestic '.$number, 'EUR', '50.00', 'domestic');
        }

        $this->get('/en/tours?scope=domestic&page=2')
            ->assertOk()
            ->assertSee('scope=domestic', false)
            ->assertSee('page=2', false);
    }

    public function test_a_fixed_discount_does_not_invent_a_percent_and_past_departures_are_not_current(): void
    {
        $editor = User::factory()->create();
        $this->actingAs($editor)->post('/admin/tours', [
            'scope' => 'domestic',
            'is_published' => '1',
            'currency' => 'USD',
            'base_price' => '100.00',
            'discount_price' => '90.00',
            'translations' => ['en' => ['title' => 'Fixed discount']],
        ])->assertRedirect();

        $this->get('/en/tours/fixed-discount')
            ->assertOk()
            ->assertSee('90.00 USD')
            ->assertSee('100.00 USD')
            ->assertSee('Discount')
            ->assertDontSee('% less');

        $past = $this->tour('Past only', 'EUR', '40.00');
        $past->departures()->create([
            'departure_date' => '2020-01-01',
            'return_date' => '2020-01-02',
            'is_published' => true,
        ]);
        $open = $this->tour('Open dates', 'EUR', '40.00');

        $this->get('/en/tours?available=1')
            ->assertOk()
            ->assertSee('Open dates')
            ->assertDontSee('Past only');
    }

    public function test_consultation_requests_are_stored_and_are_not_called_reservations(): void
    {
        $tour = $this->tour('Question tour', 'EUR', '80.00');

        $this->from('/en/consultation')->post('/en/consultation', [
            'name' => '',
            'email' => 'bad',
            'message' => '',
        ])->assertRedirect('/en/consultation')->assertSessionHasErrors(['name', 'email', 'message', 'consent']);

        $this->post('/en/consultation', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'preferred_language' => 'en',
            'tour_id' => $tour->id,
            'travelers' => 2,
            'message' => 'Is the walking pace gentle?',
            'consent' => '1',
            'company_website' => 'https://spam.example',
        ])->assertRedirect('/en/consultation');
        $this->assertSame(0, Inquiry::query()->count());

        $this->followingRedirects()->post('/en/consultation', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'preferred_language' => 'en',
            'tour_id' => $tour->id,
            'travelers' => 2,
            'message' => 'Is the walking pace gentle?',
            'consent' => '1',
        ])->assertOk()->assertSee('Your consultation request has been stored. It is not a reservation.');

        $inquiry = Inquiry::query()->firstOrFail();
        $this->assertSame('new', $inquiry->status);
        $this->assertTrue($inquiry->consent);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->put('/admin/inquiries/'.$inquiry->id, [
            'status' => 'assigned',
            'assigned_to' => $admin->id,
            'admin_note' => 'Call tomorrow.',
        ])->assertRedirect();
        $this->assertSame('assigned', $inquiry->fresh()->status);
        $this->assertSame($admin->id, $inquiry->fresh()->assigned_to);
    }

    public function test_an_empty_image_field_does_not_block_saving_a_tour(): void
    {
        $editor = User::factory()->create();
        $empty = new \Illuminate\Http\UploadedFile(
            tempnam(sys_get_temp_dir(), 'up'),
            'blank.jpg',
            'image/jpeg',
            \UPLOAD_ERR_NO_FILE,
            true
        );

        $this->actingAs($editor)->post('/admin/tours', [
            'scope' => 'domestic',
            'is_published' => '1',
            'currency' => 'EUR',
            'base_price' => '50',
            'translations' => ['en' => ['title' => 'No photo']],
            'images' => [$empty],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertTrue(Tour::query()->whereHas('translations', fn ($query) => $query->where('slug', 'no-photo'))->exists());
    }

    public function test_a_tour_can_store_an_accommodation_arrangement(): void
    {
        $editor = User::factory()->create();
        $hotel = Hotel::query()->create([
            'accommodation_category' => 'traditional house',
            'is_published' => true,
            'position' => 0,
        ]);
        $hotel->translations()->create([
            'locale' => 'en',
            'name' => 'Lane house',
            'slug' => 'lane-house',
        ]);

        $this->actingAs($editor)->post('/admin/tours', [
            'scope' => 'domestic',
            'is_published' => '1',
            'currency' => 'EUR',
            'base_price' => '200',
            'discount_percent' => '10',
            'duration_days' => 3,
            'duration_nights' => 2,
            'translations' => ['en' => ['title' => 'With stay']],
            'departures' => [
                ['departure_date' => '2026-11-02', 'return_date' => '2026-11-04', 'is_published' => '1'],
            ],
            'stays' => [
                ['hotel_id' => $hotel->id, 'translations' => ['en' => ['arrangement' => 'Two nights']]],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $tour = Tour::query()->whereHas('translations', fn ($query) => $query->where('slug', 'with-stay'))->firstOrFail();
        $this->assertSame($hotel->id, $tour->stays()->firstOrFail()->hotel_id);
        $this->assertSame('Two nights', $tour->stays()->firstOrFail()->translations()->where('locale', 'en')->firstOrFail()->arrangement);
    }

    public function test_sitemap_lists_published_translations_and_not_filter_urls(): void
    {
        $this->tour('Mapped tour', 'EUR', '70.00');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/en/tours/mapped-tour')
            ->assertDontSee('scope=');

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin');
        $this->get('/en/tours/unpublished-title')->assertNotFound();
    }

    private function tour(string $title, string $currency, string $price, string $scope = 'international'): Tour
    {
        $tour = Tour::query()->create([
            'scope' => $scope,
            'is_published' => true,
            'currency' => $currency,
            'base_price' => $price,
            'position' => 1,
        ]);
        $tour->translations()->create([
            'locale' => 'en',
            'title' => $title,
            'slug' => \App\Support\LocaleText::slug($title),
        ]);

        return $tour;
    }
}
