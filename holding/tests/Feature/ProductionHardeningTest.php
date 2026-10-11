<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Setting;
use App\Providers\AppServiceProvider;
use App\Support\ImageStore;
use App\Support\SecureUrls;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    public function test_public_responses_send_security_headers_and_the_root_redirects_permanently(): void
    {
        $this->get('/')
            ->assertStatus(301)
            ->assertRedirect('/fa');

        $this->get('/fa')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertSee('Disallow: /admin', false);
    }

    public function test_an_https_app_url_forces_https_links_and_a_secure_session_cookie(): void
    {
        config(['app.url' => 'https://holding.example.test']);
        SecureUrls::apply();

        $response = $this->get('/fa');

        $response->assertOk()->assertSee('rel="canonical" href="https://', false);
        $this->assertTrue(config('session.secure'));

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($item) => $item->getName() === config('session.cookie'));

        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isSecure());
    }

    public function test_structured_data_escapes_a_title_that_closes_the_script_tag(): void
    {
        $project = Project::query()->create(['is_published' => true, 'position' => 1]);
        $project->translations()->create([
            'locale' => 'en',
            'title' => '</script><script>alert(1)</script>',
            'slug' => 'script-title',
        ]);

        $this->get('/en/projects/script-title')
            ->assertOk()
            ->assertDontSee('</script><script>', false)
            ->assertSee('\u003C/script\u003E', false);
    }

    public function test_a_jpeg_uploaded_with_a_php_name_is_stored_as_an_image(): void
    {
        Storage::fake('public');
        $path = tempnam(sys_get_temp_dir(), 'img');
        $image = imagecreatetruecolor(8, 8);
        imagejpeg($image, $path);
        imagedestroy($image);

        $stored = ImageStore::store(new UploadedFile($path, 'shell.php', 'application/x-php', null, true), 'covers');

        $this->assertStringEndsWith('.jpg', $stored);
        foreach (Storage::disk('public')->allFiles('covers') as $file) {
            $this->assertDoesNotMatchRegularExpression('/\.(php|phtml|phar)$/i', $file);
        }
        $this->assertNotEmpty(Storage::disk('public')->allFiles('covers'));
        $this->assertTrue(collect(Storage::disk('public')->allFiles('covers'))->contains(
            fn (string $file) => str_ends_with($file, '-source.jpg')
        ));
    }

    public function test_settings_are_read_once_and_refresh_after_a_direct_save(): void
    {
        Setting::putValue('contact_email', 'desk@example.com');
        Setting::getValue('contact_email');

        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->assertSame('desk@example.com', Setting::getValue('contact_email'));
        $this->assertNull(Setting::getValue('contact_phone'));
        $this->assertSame([], DB::getQueryLog());

        Setting::query()->updateOrCreate(['key' => 'contact_email'], ['value' => 'next@example.com']);

        $this->assertSame('next@example.com', Setting::getValue('contact_email'));
    }

    public function test_a_custom_public_path_is_used_when_the_web_root_is_separate(): void
    {
        config(['karako.public_path' => '/tmp/karako-holding-web']);
        (new AppServiceProvider($this->app))->register();

        $this->assertSame('/tmp/karako-holding-web', public_path());
    }
}
