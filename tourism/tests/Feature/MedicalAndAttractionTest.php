<?php

namespace Tests\Feature;

use App\Models\Attraction;
use App\Models\AttractionCategory;
use App\Models\Destination;
use App\Models\MedicalInquiry;
use App\Models\MedicalService;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MedicalAndAttractionTest extends TestCase
{
    public function test_medical_content_stays_in_its_language_and_does_not_invent_clinical_claims(): void
    {
        $editor = User::factory()->create();
        $this->get('/admin/medical')->assertRedirect(route('admin.login'));

        $this->actingAs($editor)->post('/admin/medical/categories', [
            'is_published' => '1',
            'position' => 1,
            'translations' => [
                'en' => ['name' => 'Checkups', 'summary' => 'A category entered for the test.'],
                'fa' => ['name' => 'بررسی سلامت', 'summary' => 'دسته آزمایشی'],
            ],
        ])->assertRedirect();

        $this->actingAs($editor)->post('/admin/medical/clinics', [
            'is_published' => '1',
            'translations' => ['en' => ['name' => 'North clinic', 'summary' => 'A clinic name entered for the test.']],
        ])->assertRedirect();
        $providerId = \App\Models\MedicalProvider::query()->value('id');

        $this->actingAs($editor)->post('/admin/medical/services', [
            'is_published' => '1',
            'medical_category_id' => \App\Models\MedicalCategory::query()->value('id'),
            'provider_ids' => [$providerId],
            'cover' => $this->jpeg('cover.jpg'),
            'images' => [$this->jpeg('room.jpg')],
            'translations' => [
                'en' => [
                    'name' => 'Health review',
                    'summary' => 'English summary of the entered service.',
                    'description' => 'English only description sentence.',
                    'cover_alt' => 'Clinic courtyard',
                    'meta_title' => 'Health review title',
                    'meta_description' => 'Health review description',
                ],
                'fa' => [
                    'name' => 'بررسی سلامت',
                    'summary' => 'خلاصه فارسی',
                    'description' => 'توضیح فارسی خدمت',
                ],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $service = MedicalService::query()->firstOrFail();
        $image = $service->images()->firstOrFail();
        $this->actingAs($editor)->put('/admin/medical/services/'.$service->id, [
            'is_published' => '1',
            'medical_category_id' => $service->medical_category_id,
            'provider_ids' => [$providerId],
            'translations' => [
                'en' => [
                    'name' => 'Health review',
                    'description' => 'English only description sentence.',
                    'cover_alt' => 'Clinic courtyard',
                    'meta_title' => 'Health review title',
                    'meta_description' => 'Health review description',
                ],
                'fa' => ['name' => 'بررسی سلامت', 'description' => 'توضیح فارسی خدمت'],
            ],
            'images_meta' => [
                $image->id => ['translations' => ['en' => ['alt' => 'Treatment room', 'caption' => 'Room entered for the test'], 'fa' => ['alt' => 'اتاق درمان']]],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->actingAs($editor)->post('/admin/medical/doctors', [
            'is_published' => '1',
            'medical_provider_id' => $providerId,
            'translations' => ['en' => ['name' => 'Recorded physician', 'specialty' => 'Entered specialty', 'biography' => 'Biography entered by the team.']],
        ])->assertRedirect();

        $this->actingAs($editor)->post('/admin/medical/steps', [
            'is_published' => '1',
            'position' => 1,
            'translations' => ['en' => ['title' => 'Send a question', 'body' => 'The team reads the question.']],
        ])->assertRedirect();

        $this->actingAs($editor)->put('/admin/medical/page', [
            'translations' => [
                'en' => ['title' => 'Medical notes', 'travel_assistance' => 'Travel text entered in English.', 'contact_note' => 'Write to the recorded address.'],
                'fa' => ['title' => 'یادداشت درمان'],
            ],
        ])->assertRedirect();

        $this->get('/fa/medical/services/health-review')->assertNotFound();
        $persian = $service->translations()->where('locale', 'fa')->firstOrFail();
        $this->get('/en/medical/services/health-review')
            ->assertOk()
            ->assertSee('Health review title')
            ->assertSee('English only description sentence')
            ->assertSee('Clinic courtyard')
            ->assertSee('Treatment room')
            ->assertSee('Room entered for the test')
            ->assertSee('North clinic')
            ->assertSee('This section is informational')
            ->assertDontSee('توضیح فارسی خدمت')
            ->assertDontSee('guaranteed')
            ->assertDontSee('type="file"', false);
        $this->get('/fa/medical/services/'.$persian->slug)
            ->assertOk()
            ->assertSee('توضیح فارسی خدمت')
            ->assertDontSee('English only description sentence')
            ->assertSee('hreflang="ru" href="'.route('home', ['locale' => 'ru']).'"', false);

        $this->get('/en/medical')
            ->assertOk()
            ->assertSee('Travel text entered in English.')
            ->assertSee('Send a question')
            ->assertDontSee('یادداشت درمان');
        $this->get('/fa/medical')
            ->assertOk()
            ->assertSee('یادداشت درمان')
            ->assertDontSee('Travel text entered in English.');

        $this->get('/en/medical/doctors/recorded-physician')
            ->assertOk()
            ->assertSee('Entered specialty')
            ->assertSee('This profile shows only the text entered by the team.')
            ->assertDontSee('board certified');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/en/medical/services/health-review')
            ->assertDontSee('/fa/medical/services/health-review');
    }

    public function test_a_medical_question_is_private_and_does_not_store_a_honeypot_or_an_unpublished_service(): void
    {
        $service = MedicalService::query()->create(['is_published' => true, 'position' => 1]);
        $service->translations()->create(['locale' => 'en', 'name' => 'Health review', 'slug' => 'health-review']);

        $this->from('/en/medical')->post('/en/medical/consultation', [
            'name' => 'Nila',
            'email' => 'nila@example.com',
            'preferred_language' => 'en',
            'country' => 'Armenia',
            'medical_service_id' => $service->id,
            'message' => 'What documents should I bring?',
            'consent' => '1',
        ])->assertRedirect('/en/medical');

        $inquiry = MedicalInquiry::query()->firstOrFail();
        $this->assertSame('Armenia', $inquiry->country);
        $this->assertSame($service->id, $inquiry->medical_service_id);
        $this->assertSame('new', $inquiry->status);

        $this->post('/en/medical/consultation', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'preferred_language' => 'en',
            'message' => 'Ignore',
            'consent' => '1',
            'company_website' => 'https://spam.example',
        ])->assertRedirect();
        $this->assertSame(1, MedicalInquiry::query()->count());

        $this->from('/en/medical')->post('/en/medical/consultation', [
            'name' => 'Nila',
            'preferred_language' => 'en',
            'message' => 'Missing email',
            'consent' => '1',
        ])->assertSessionHasErrors('email');

        $editor = User::factory()->create();
        $this->actingAs($editor)->delete('/admin/medical/inquiries/'.$inquiry->id)->assertForbidden();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->delete('/admin/medical/inquiries/'.$inquiry->id, ['confirm_delete' => '1'])->assertRedirect('/admin/medical/inquiries');
        $this->assertSame(0, MedicalInquiry::query()->count());
    }

    public function test_unesco_wording_appears_only_after_verification_and_filters_stay_out_of_the_index(): void
    {
        $editor = User::factory()->create();
        $country = Destination::query()->create(['type' => 'country', 'is_published' => true, 'position' => 1]);
        $country->translations()->create(['locale' => 'en', 'name' => 'Testland', 'slug' => 'testland']);
        $city = Destination::query()->create(['type' => 'city', 'parent_id' => $country->id, 'is_published' => true, 'position' => 1]);
        $city->translations()->create(['locale' => 'en', 'name' => 'Sample city', 'slug' => 'sample-city']);

        $this->actingAs($editor)->post('/admin/attraction-categories', [
            'is_published' => '1',
            'translations' => ['en' => ['name' => 'Gardens']],
        ])->assertRedirect();
        $categoryId = AttractionCategory::query()->value('id');

        $this->actingAs($editor)->post('/admin/attractions', [
            'is_published' => '1',
            'destination_id' => $city->id,
            'attraction_category_id' => $categoryId,
            'classification' => 'cultural',
            'unesco_designation' => 'world_heritage',
            'unesco_verified' => '0',
            'inscribed_on' => '1979-01-01',
            'unesco_reference' => '114',
            'latitude' => '35.7000',
            'longitude' => '51.4000',
            'translations' => [
                'en' => [
                    'title' => 'Sample garden',
                    'summary' => 'A place entered for the test.',
                    'description' => 'Detailed description entered by the team.',
                    'visitor_info' => 'Open in the morning.',
                    'meta_title' => 'Sample garden title',
                    'meta_description' => 'Sample garden description',
                ],
                'fa' => ['title' => 'باغ نمونه', 'summary' => 'خلاصه فارسی باغ'],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->get('/en/attractions/sample-garden')
            ->assertOk()
            ->assertSee('Sample garden title')
            ->assertSee('Cultural')
            ->assertSee('Sample city')
            ->assertSee('Testland')
            ->assertSee('Open in the morning.')
            ->assertSee('35.7')
            ->assertDontSee('UNESCO World Heritage Site')
            ->assertDontSee('1979-01-01');

        $attraction = Attraction::query()->firstOrFail();
        $this->actingAs($editor)->put('/admin/attractions/'.$attraction->id, [
            'is_published' => '1',
            'destination_id' => $city->id,
            'attraction_category_id' => $categoryId,
            'classification' => 'cultural',
            'unesco_designation' => 'world_heritage',
            'unesco_verified' => '1',
            'inscribed_on' => '1979-01-01',
            'unesco_reference' => '114',
            'latitude' => '35.7000',
            'longitude' => '51.4000',
            'translations' => [
                'en' => ['title' => 'Sample garden', 'summary' => 'A place entered for the test.'],
                'fa' => ['title' => 'باغ نمونه'],
            ],
        ])->assertSessionHasNoErrors();

        $this->get('/en/attractions/sample-garden')
            ->assertOk()
            ->assertSee('UNESCO World Heritage Site')
            ->assertSee('1979-01-01')
            ->assertSee('114');
        $persianPlace = $attraction->translations()->where('locale', 'fa')->firstOrFail();
        $this->get('/fa/attractions/'.$persianPlace->slug)
            ->assertOk()
            ->assertSee('میراث جهانی یونسکو')
            ->assertDontSee('A place entered for the test.');

        $other = Attraction::query()->create([
            'destination_id' => $country->id,
            'is_published' => true,
            'classification' => 'natural',
            'unesco_designation' => 'none',
            'unesco_verified' => false,
        ]);
        $other->translations()->create(['locale' => 'en', 'title' => 'Other ridge', 'slug' => 'other-ridge', 'summary' => 'Another entered place.']);

        $this->get('/en/attractions?country='.$country->id.'&category='.$categoryId)
            ->assertOk()
            ->assertSee('Sample garden')
            ->assertDontSee('Other ridge')
            ->assertSee('noindex, follow', false);
        $this->get('/en/attractions?country=99999')->assertRedirect('/en/attractions');
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/en/attractions/sample-garden')
            ->assertDontSee('country='.$country->id);
    }

    public function test_publishing_requires_a_title_and_coordinates_must_be_a_pair(): void
    {
        $editor = User::factory()->create();
        $this->actingAs($editor)->from('/admin/attractions/create')->post('/admin/attractions', [
            'is_published' => '1',
            'latitude' => '1',
            'translations' => ['en' => ['title' => '']],
        ])->assertSessionHasErrors(['is_published', 'latitude']);
    }

    private function jpeg(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'karako');
        $image = imagecreatetruecolor(12, 8);
        imagejpeg($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, $name, 'image/jpeg', null, true);
    }
}
