<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\HomepageTranslation;
use App\Models\Inquiry;
use App\Models\Page;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HoldingWebsiteTest extends TestCase
{
    public function test_divisions_follow_the_brief_and_tourism_links_to_its_site(): void
    {
        config(['karako.related_url' => 'https://travel.example']);
        $this->seed();

        $this->get('/en')
            ->assertOk()
            ->assertSeeInOrder([
                'Tourism and advertising agency',
                'Real estate',
                'Commercial services',
                'Agriculture',
                'Technical and engineering services',
                'Legal and administrative services',
                'General commerce and trading',
                'Construction and building',
                'Social activities and charitable initiatives',
            ])
            ->assertSee('https://travel.example', false)
            ->assertSee('media/preview/division-tourism.jpg', false)
            ->assertSee('media/preview/division-construction.jpg', false)
            ->assertSee('href="'.route('divisions.show', ['locale' => 'en', 'slug' => 'tourism']).'"', false);

        $this->get('/fa')
            ->assertOk()
            ->assertSeeInOrder([
                'آژانس گردشگری و تبلیغات',
                'املاک و مستغلات',
                'خدمات بازرگانی',
                'کشاورزی',
                'خدمات فنی و مهندسی',
                'خدمات حقوقی و اداری',
                'بازرگانی و تجارت عمومی',
                'ساخت‌وساز و ساختمان',
                'فعالیت‌های اجتماعی و نیکوکاری',
            ]);
    }

    public function test_unpublished_divisions_and_empty_optional_sections_stay_hidden(): void
    {
        $this->seed();
        Division::query()->where('key', 'legal')->update(['is_published' => false]);

        $this->get('/en')
            ->assertOk()
            ->assertDontSee('Legal and administrative services')
            ->assertDontSee('<video', false)
            ->assertDontSee('<iframe', false);

        $this->get('/en/divisions/legal-services')->assertNotFound();
        $this->get('/en/projects')->assertNotFound();
        $this->get('/en/about')->assertNotFound();
    }

    public function test_homepage_falls_back_to_english_and_points_canonical_there(): void
    {
        HomepageTranslation::query()->create([
            'locale' => 'en',
            'hero_title' => 'English hero',
            'meta_description' => 'English description',
        ]);

        $this->get('/fa')
            ->assertOk()
            ->assertSee('English hero')
            ->assertSee('rel="canonical" href="'.route('home', ['locale' => 'en']).'"', false);

        $this->get('/en')
            ->assertOk()
            ->assertSee('<title>English hero — KARAKO</title>', false)
            ->assertSee('English description');
    }

    public function test_about_and_custom_pages_respect_locale_and_publication(): void
    {
        $about = Page::query()->create(['key' => 'about', 'is_published' => true, 'position' => 1]);
        $about->translations()->create([
            'locale' => 'en',
            'title' => 'About the holding',
            'slug' => 'about',
            'body' => 'English about body',
        ]);

        $this->get('/fa/about')
            ->assertOk()
            ->assertSee('About the holding')
            ->assertSee('rel="canonical" href="'.route('about', ['locale' => 'en']).'"', false);

        $page = Page::query()->create(['is_published' => true, 'position' => 2]);
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Privacy',
            'slug' => 'privacy',
            'body' => 'Privacy text',
        ]);

        $this->get('/en/info/privacy')->assertOk()->assertSee('Privacy text');
        $this->get('/fa/info/privacy')->assertNotFound();

        $page->update(['is_published' => false]);
        $this->get('/en/info/privacy')->assertNotFound();
    }

    public function test_projects_appear_only_after_they_are_published(): void
    {
        $project = Project::query()->create(['is_published' => true, 'position' => 1]);
        $project->translations()->create([
            'locale' => 'en',
            'title' => 'North building',
            'slug' => 'north-building',
            'summary' => 'A configured project',
            'body' => 'Project body',
        ]);

        $this->get('/en')->assertOk()->assertSee('A configured project');
        $this->get('/en/projects')->assertOk()->assertSee('North building');
        $this->get('/en/projects/north-building')->assertOk()->assertSee('Project body');
        $this->get('/fa/projects/north-building')->assertNotFound();
    }

    public function test_language_switcher_uses_the_translation_slug(): void
    {
        $this->seed();
        Division::query()->where('key', 'tourism')->first()
            ->translations()->where('locale', 'fa')->update(['slug' => 'gardeshgari']);

        $this->get('/en/divisions/tourism')
            ->assertOk()
            ->assertSee('href="'.route('divisions.show', ['locale' => 'fa', 'slug' => 'gardeshgari']).'"', false);
    }

    public function test_video_is_omitted_until_a_supported_source_is_configured(): void
    {
        Setting::query()->updateOrCreate(['key' => 'video_type'], ['value' => 'url']);
        Setting::query()->updateOrCreate(['key' => 'video_url'], ['value' => 'https://example.com/watch?v=abc_def123']);

        $this->get('/en')->assertOk()->assertDontSee('<iframe', false);

        Setting::query()->updateOrCreate(['key' => 'video_url'], ['value' => 'https://www.youtube.com/watch?v=abc_def123']);

        $this->get('/en')
            ->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/abc_def123?autoplay=0&amp;rel=0', false)
            ->assertDontSee('autoplay=1', false);

        Setting::query()->updateOrCreate(['key' => 'video_type'], ['value' => 'file']);
        Setting::query()->updateOrCreate(['key' => 'video_path'], ['value' => 'videos/intro.mp4']);

        $html = $this->get('/en')->assertOk()->assertSee('<video', false)->assertSee('controls', false)->getContent();
        $this->assertStringNotContainsString('autoplay', $html);
    }

    public function test_contact_form_stores_a_message_and_ignores_the_honeypot(): void
    {
        $this->post('/en/contact', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'message' => 'Hello',
            'company_website' => 'https://spam.example',
        ])->assertRedirect('/en/contact');

        $this->assertSame(0, Inquiry::query()->count());

        $this->post('/en/contact', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'message' => 'Hello from the form',
        ])->assertRedirect('/en/contact');

        $this->assertDatabaseHas('inquiries', [
            'email' => 'ada@example.com',
            'message' => 'Hello from the form',
            'locale' => 'en',
        ]);
    }

    public function test_sitemap_and_robots_list_published_pages_only(): void
    {
        $this->seed();
        Division::query()->where('key', 'legal')->update(['is_published' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee(route('divisions.show', ['locale' => 'en', 'slug' => 'tourism']), false)
            ->assertDontSee('legal-services', false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('/sitemap.xml');
    }

    public function test_structured_data_uses_only_configured_facts(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"name":"KARAKO"', false)
            ->assertDontSee('telephone', false)
            ->assertDontSee('address', false);

        Setting::query()->updateOrCreate(['key' => 'company_name'], ['value' => 'KARAKO Holding']);
        Setting::query()->updateOrCreate(['key' => 'contact_email'], ['value' => 'desk@example.com']);

        $this->get('/en')
            ->assertOk()
            ->assertSee('"name":"KARAKO Holding"', false)
            ->assertSee('"email":"desk@example.com"', false);
    }

    public function test_admin_can_publish_homepage_copy_and_reject_an_unsafe_video(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put('/admin/homepage', [
            'video_type' => 'url',
            'video_url' => 'https://example.com/clip',
        ])->assertSessionHasErrors('video_url');

        $this->actingAs($admin)->put('/admin/homepage', [
            'video_type' => 'none',
            'hero_image' => UploadedFile::fake()->image('hero.jpg', 40, 30),
            'translations' => [
                'en' => [
                    'hero_title' => 'A diversified holding',
                    'intro_heading' => 'The company',
                    'intro_body' => 'A short introduction.',
                    'approach_heading' => 'Approach',
                    'approach_body' => 'Only this entered text.',
                ],
            ],
        ])->assertRedirect(route('admin.homepage.edit'));

        $path = Setting::getValue('hero_image');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->get('/en')
            ->assertOk()
            ->assertSee('A diversified holding')
            ->assertSee('A short introduction.')
            ->assertSee('Only this entered text.')
            ->assertSee($path, false)
            ->assertDontSee('hero-holding.jpg', false)
            ->assertSee('stage-holding.jpg', false);
    }

    public function test_guests_and_editors_are_kept_out_of_user_management(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');

        $editor = User::factory()->create();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin')->assertOk();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'editor',
        ])->assertSessionHasErrors('role');
    }

    public function test_an_editor_can_reorder_divisions_and_read_an_inquiry(): void
    {
        $this->seed();
        $editor = User::factory()->create();
        $tourism = Division::query()->where('key', 'tourism')->first();
        $realEstate = Division::query()->where('key', 'real-estate')->first();

        $payload = [];
        foreach (Division::query()->get() as $division) {
            $payload[$division->id] = [
                'position' => $division->position,
                'is_published' => $division->is_published ? '1' : '0',
            ];
        }
        $payload[$tourism->id]['position'] = 2;
        $payload[$realEstate->id]['position'] = 1;

        $this->actingAs($editor)->put('/admin/divisions', ['divisions' => $payload])
            ->assertRedirect(route('admin.divisions.index'));

        $this->assertSame(2, $tourism->fresh()->position);
        $this->assertSame(1, $realEstate->fresh()->position);

        $this->post('/en/contact', [
            'name' => 'Neda',
            'email' => 'neda@example.com',
            'message' => 'A stored inquiry',
        ]);

        $inquiry = Inquiry::query()->first();
        $this->actingAs($editor)->get(route('admin.inquiries.show', $inquiry))
            ->assertOk()
            ->assertSee('A stored inquiry')
            ->assertSee('neda@example.com');
    }
}
