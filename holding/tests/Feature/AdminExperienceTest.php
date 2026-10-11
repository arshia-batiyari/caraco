<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Page;
use App\Models\User;
use Tests\TestCase;

class AdminExperienceTest extends TestCase
{
    public function test_the_dashboard_groups_navigation_and_editors_cannot_open_users(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->get('/admin')
            ->assertOk()
            ->assertSee(__('admin.groups.overview'))
            ->assertSee(__('admin.groups.content'))
            ->assertSee(__('admin.groups.messages'))
            ->assertDontSee('href="'.route('admin.users.index').'"', false);

        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
    }

    public function test_an_editor_can_publish_and_unpublish_a_page_without_showing_a_missing_translation(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->post('/admin/pages', [
            'position' => 3,
            'is_published' => '0',
            'translations' => [
                'en' => [
                    'title' => 'North note',
                    'slug' => 'north-note',
                    'body' => 'Entered body',
                    'meta_title' => 'North note title',
                    'meta_description' => 'North note description',
                ],
            ],
        ])->assertRedirect();

        $page = Page::query()->first();
        $this->assertFalse($page->is_published);
        $this->get('/en/info/north-note')->assertNotFound();

        $this->actingAs($editor)->put('/admin/pages/'.$page->id, [
            'position' => 3,
            'is_published' => '1',
            'translations' => [
                'en' => [
                    'title' => 'North note',
                    'slug' => 'north-note',
                    'body' => 'Entered body',
                    'meta_title' => 'North note title',
                    'meta_description' => 'North note description',
                ],
            ],
        ])->assertRedirect();

        $this->get('/en/info/north-note')->assertOk()->assertSee('Entered body');
        $this->get('/fa/info/north-note')->assertNotFound();

        $this->actingAs($editor)->get('/admin/pages?q=North')
            ->assertOk()
            ->assertSee('North note')
            ->assertSee(__('admin.published'));

        $this->actingAs($editor)->put('/admin/pages/'.$page->id, [
            'position' => 3,
            'is_published' => '0',
            'translations' => [
                'en' => [
                    'title' => 'North note',
                    'slug' => 'north-note',
                    'body' => 'Entered body',
                ],
            ],
        ])->assertRedirect();

        $this->get('/en/info/north-note')->assertNotFound();
        $this->actingAs($editor)->get('/admin/pages?published=0')
            ->assertOk()
            ->assertSee('North note')
            ->assertSee(__('admin.draft'));
    }

    public function test_inquiries_can_be_searched_and_pages_need_delete_confirmation(): void
    {
        $editor = User::factory()->create();
        Inquiry::query()->create([
            'name' => 'Neda Karimi',
            'email' => 'neda@example.com',
            'message' => 'A stored inquiry',
            'locale' => 'fa',
        ]);
        Inquiry::query()->create([
            'name' => 'Other Person',
            'email' => 'other@example.com',
            'message' => 'Different note',
            'locale' => 'en',
        ]);

        $this->actingAs($editor)->get('/admin/inquiries?q=Neda')
            ->assertOk()
            ->assertSee('Neda Karimi')
            ->assertDontSee('Other Person');

        $page = Page::query()->create(['is_published' => false, 'position' => 4]);
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Temporary',
            'slug' => 'temporary',
        ]);

        $this->actingAs($editor)->from('/admin/pages/'.$page->id)
            ->delete('/admin/pages/'.$page->id)
            ->assertRedirect('/admin/pages/'.$page->id)
            ->assertSessionHasErrors('confirm_delete');
        $this->assertModelExists($page);

        $this->actingAs($editor)->delete('/admin/pages/'.$page->id, ['confirm_delete' => '1'])
            ->assertRedirect('/admin/pages');
        $this->assertModelMissing($page);
    }
}
