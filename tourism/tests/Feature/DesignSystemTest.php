<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    public function test_persian_homepage_is_right_to_left(): void
    {
        $this->get('/fa')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('KARAKO')
            ->assertDontSee('fonts.bunny.net')
            ->assertDontSee('<video', false)
            ->assertDontSee('youtube');
    }

    public function test_other_locales_render_their_own_direction(): void
    {
        $this->get('/en')->assertOk()->assertSee('dir="ltr"', false)->assertSee('Find a tour');
        $this->get('/ru')->assertOk()->assertSee('dir="ltr"', false)->assertSee('Найти тур');
        $this->get('/ar')->assertOk()->assertSee('dir="rtl"', false)->assertSee('ابحث عن جولة');
    }

    public function test_foundations_credits_and_missing_pages(): void
    {
        $this->get('/fa/foundations?page=2')->assertOk()->assertSee('یادداشت 5');
        $this->get('/ru/not-a-page')->assertNotFound()->assertSee('Эта страница недоступна.');
        $this->get('/en/credits')->assertOk()->assertSee('Diego Delso')->assertSee('noindex, follow', false);
    }
}
