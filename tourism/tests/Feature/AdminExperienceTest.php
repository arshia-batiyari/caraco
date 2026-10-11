<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Page;
use App\Models\Tour;
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
            ->assertSee(__('admin.groups.catalog'))
            ->assertSee(__('admin.groups.messages'))
            ->assertDontSee('href="'.route('admin.users.index').'"', false);

        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
    }

    public function test_catalog_lists_can_be_searched_and_filtered_by_publication(): void
    {
        $editor = User::factory()->create();
        $published = Tour::query()->create([
            'scope' => 'domestic',
            'is_published' => true,
            'position' => 1,
        ]);
        $published->translations()->create([
            'locale' => 'fa',
            'title' => 'باغ تاریخی',
            'slug' => 'bagh',
        ]);
        $draft = Tour::query()->create([
            'scope' => 'international',
            'is_published' => false,
            'position' => 2,
        ]);
        $draft->translations()->create([
            'locale' => 'en',
            'title' => 'Coast walk',
            'slug' => 'coast',
        ]);

        $this->actingAs($editor)->get('/admin/tours?q='.urlencode('باغ'))
            ->assertOk()
            ->assertSee('باغ تاریخی')
            ->assertDontSee('Coast walk');

        $this->actingAs($editor)->get('/admin/tours?published=0')
            ->assertOk()
            ->assertSee('Coast walk')
            ->assertSee(__('admin.draft'))
            ->assertDontSee('باغ تاریخی');
    }

    public function test_inquiry_lists_search_name_and_status(): void
    {
        $editor = User::factory()->create();
        Inquiry::query()->create([
            'name' => 'Neda Karimi',
            'email' => 'neda@example.com',
            'locale' => 'fa',
            'preferred_language' => 'fa',
            'message' => 'A private question',
            'consent' => true,
            'status' => 'new',
        ]);
        Inquiry::query()->create([
            'name' => 'Other Person',
            'email' => 'other@example.com',
            'locale' => 'en',
            'preferred_language' => 'en',
            'message' => 'Another note',
            'consent' => true,
            'status' => 'closed',
        ]);

        $this->actingAs($editor)->get('/admin/inquiries?q=Neda')
            ->assertOk()
            ->assertSee('Neda Karimi')
            ->assertDontSee('Other Person');

        $this->actingAs($editor)->get('/admin/inquiries?status=closed')
            ->assertOk()
            ->assertSee('Other Person')
            ->assertDontSee('Neda Karimi');
    }

    public function test_deleting_a_page_requires_confirmation(): void
    {
        $editor = User::factory()->create();
        $page = Page::query()->create([
            'is_published' => false,
            'position' => 1,
        ]);
        $page->translations()->create([
            'locale' => 'en',
            'title' => 'Notes',
            'slug' => 'notes',
            'body' => 'Stored body',
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
