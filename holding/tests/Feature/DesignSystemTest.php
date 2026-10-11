<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    public function test_persian_homepage_is_right_to_left_and_uses_local_copy(): void
    {
        $this->get('/fa')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('lang="fa"', false)
            ->assertSee('brand/logo.png', false)
            ->assertSee('alt="CARACO"', false)
            ->assertSee('KARAKO')
            ->assertSee('تماس با ما')
            ->assertDontSee('نُه زمینهٔ کار، با یک معیار.')
            ->assertDontSee('fonts.bunny.net')
            ->assertDontSee('fonts.googleapis.com')
            ->assertDontSee('<video', false)
            ->assertDontSee('<iframe', false);
    }

    public function test_english_and_russian_are_left_to_right(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('brand/logo.png', false)
            ->assertSee('alt="CARACO"', false)
            ->assertSee('KARAKO')
            ->assertDontSee('Nine fields of work, held to one standard.')
            ->assertDontSee('Sample interface');

        $this->get('/ru')
            ->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('Связаться');

        $this->get('/ar')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('تواصل معنا');
    }

    public function test_contact_form_reports_validation_errors_and_accepts_a_message(): void
    {
        $this->from('/en/contact')
            ->post('/en/contact', ['name' => '', 'email' => 'not-an-email', 'message' => ''])
            ->assertRedirect('/en/contact')
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->followingRedirects()
            ->post('/en/contact', [
                'name' => 'Ada',
                'email' => 'ada@example.com',
                'message' => 'A note for the holding.',
            ])
            ->assertOk()
            ->assertSee('Your message has been stored.')
            ->assertDontSee('Nothing was stored or sent.');

        $this->assertDatabaseHas('inquiries', [
            'email' => 'ada@example.com',
            'locale' => 'en',
        ]);
        $this->assertSame(1, Inquiry::query()->count());
    }

    public function test_foundations_page_paginates_and_missing_pages_use_the_locale(): void
    {
        $this->get('/en/foundations?page=2')
            ->assertOk()
            ->assertSee('Note 5')
            ->assertSee('aria-current="page"', false)
            ->assertDontSee('Note 1');

        $this->get('/en/not-a-page')
            ->assertNotFound()
            ->assertSee('This page is not available.');

        $this->get('/en/credits')
            ->assertOk()
            ->assertSee('Bernard Gagnon')
            ->assertSee('CC BY-SA 4.0');
    }

    public function test_stylesheet_does_not_depend_on_a_font_cdn(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringNotContainsString('fonts.bunny.net', $css);
        $this->assertStringNotContainsString('fonts.googleapis.com', $css);
        $this->assertStringContainsString('Vazirmatn', $css);
        $this->assertStringContainsString('Literata', $css);
    }
}
