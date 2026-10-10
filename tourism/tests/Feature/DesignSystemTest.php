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
            ->assertSee('سفر، با همان معیار گروه.')
            ->assertSee('k-header-overlay', false)
            ->assertDontSee('fonts.bunny.net');
    }

    public function test_other_locales_render_their_own_copy(): void
    {
        $this->get('/en')->assertOk()->assertSee('dir="ltr"', false)
            ->assertSee('Travel, held to the same standard as the group.');
        $this->get('/ru')->assertOk()->assertSee('Путешествия по тому же стандарту, что и группа.');
        $this->get('/ar')->assertOk()->assertSee('dir="rtl"', false)
            ->assertSee('سفر بالمعيار نفسه الذي تعتمده المجموعة.');
    }

    public function test_inquiry_form_validates_and_confirms_without_storing(): void
    {
        $this->from('/en/inquiry')
            ->post('/en/inquiry', ['name' => '', 'email' => 'bad', 'message' => ''])
            ->assertRedirect('/en/inquiry')
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->followingRedirects()
            ->post('/en/inquiry', [
                'name' => 'Ada',
                'email' => 'ada@example.com',
                'message' => 'A sample inquiry.',
            ])
            ->assertOk()
            ->assertSee('Nothing was stored or sent.');
    }

    public function test_journey_foundations_and_missing_pages(): void
    {
        $this->get('/en/journey')->assertOk()->assertSee('Four days, written as a sample route.');
        $this->get('/fa/foundations?page=2')->assertOk()->assertSee('یادداشت 5');
        $this->get('/ru/not-a-page')->assertNotFound()->assertSee('Эта страница недоступна.');
        $this->get('/en/credits')->assertOk()->assertSee('Diego Delso');
    }
}
