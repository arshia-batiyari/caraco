<?php

namespace Tests\Feature;

use App\Models\FeedbackSubmission;
use App\Models\Service;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\Tour;
use App\Models\User;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    public function test_feedback_pages_use_localized_titles_and_directions(): void
    {
        $this->get('/fa/feedback')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('بازخورد، شکایت و پیشنهاد مشتریان')
            ->assertSee('هنوز بازخورد منتشرشده‌ای نیست');

        $this->get('/en/feedback')
            ->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('Customer Feedback, Complaints & Suggestions')
            ->assertSee('No published feedback yet')
            ->assertSee('Each note stays in the language it was written.');

        $this->get('/ru/feedback')
            ->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('Отзывы, жалобы и предложения клиентов');

        $this->get('/ar/feedback')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('ملاحظات العملاء والشكاوى والاقتراحات');
    }

    public function test_a_guest_cannot_open_feedback_admin_and_an_editor_cannot_delete_personal_records(): void
    {
        $this->get('/admin/feedback')->assertRedirect(route('admin.login'));
        $this->get('/admin/surveys')->assertRedirect(route('admin.login'));

        $editor = User::factory()->create();
        $submission = $this->storeFeedback();

        $this->actingAs($editor)->delete('/admin/feedback/'.$submission->id)->assertForbidden();
        $this->assertSame(1, FeedbackSubmission::query()->count());
    }

    public function test_new_feedback_stays_private_until_an_editor_approves_it(): void
    {
        $tour = $this->tour();
        $service = Service::query()->create(['type' => 'included', 'is_published' => true, 'position' => 1]);
        $service->translations()->create(['locale' => 'en', 'name' => 'Guide']);

        $this->post('/en/feedback', $this->payload([
            'name' => 'Ada Lane',
            'title' => 'Quiet morning',
            'message' => 'The lanes were calm.',
            'tour_id' => $tour->id,
            'service_id' => $service->id,
            'rating_overall' => 5,
            'rating_accommodation' => 4,
            'email' => 'ada@example.com',
            'phone' => '02100000000',
        ]))->assertRedirect('/en/feedback')->assertSessionHas('sent', 'Your message has been stored. It is not on the public page until it is approved.');

        $submission = FeedbackSubmission::query()->firstOrFail();
        $this->assertSame('pending', $submission->review->status);
        $this->assertNull($submission->review->published_at);
        $this->assertFalse($submission->review->is_verified);

        $this->get('/en/feedback')
            ->assertOk()
            ->assertDontSee('Quiet morning')
            ->assertDontSee('ada@example.com')
            ->assertDontSee('02100000000')
            ->assertDontSee('Ada Lane');

        $editor = User::factory()->create();
        $this->actingAs($editor)->put('/admin/feedback/'.$submission->id, [
            'status' => 'approved',
            'is_verified' => '1',
            'note' => 'Checked the booking record.',
        ])->assertRedirect();

        $submission->refresh();
        $this->assertSame('approved', $submission->review->status);
        $this->assertTrue($submission->review->is_verified);
        $this->assertSame($editor->id, $submission->review->verified_by);
        $this->assertNotNull($submission->review->published_at);

        $this->get('/en/feedback')
            ->assertOk()
            ->assertSee('Quiet morning')
            ->assertSee('Ada Lane')
            ->assertSee('Verified customer')
            ->assertSee('The lanes were calm.')
            ->assertSee('Yazd lanes')
            ->assertSee('Guide')
            ->assertSee('Accommodation 4/5')
            ->assertSee('Written in English')
            ->assertDontSee('ada@example.com')
            ->assertDontSee('02100000000')
            ->assertDontSee('Checked the booking record.');

        $this->actingAs($editor)->put('/admin/feedback/'.$submission->id, [
            'status' => 'hidden',
            'is_verified' => '0',
        ])->assertRedirect();

        $this->get('/en/feedback')->assertDontSee('Quiet morning');
        $this->assertNull($submission->refresh()->review->published_at);
        $this->assertFalse($submission->review->is_verified);
    }

    public function test_anonymous_feedback_discards_the_name_and_complaints_stay_private(): void
    {
        $this->post('/en/feedback', $this->payload([
            'is_anonymous' => '1',
            'name' => 'Secret Person',
            'title' => 'Hidden name note',
            'message' => 'Please do not print my name.',
        ]))->assertRedirect();

        $anonymous = FeedbackSubmission::query()->firstOrFail();
        $this->assertNull($anonymous->name);
        $this->assertTrue($anonymous->is_anonymous);

        $editor = User::factory()->create();
        $this->actingAs($editor)->put('/admin/feedback/'.$anonymous->id, [
            'status' => 'approved',
            'is_verified' => '0',
        ])->assertRedirect();

        $this->get('/en/feedback')
            ->assertSee('Hidden name note')
            ->assertSee('Anonymous')
            ->assertDontSee('Secret Person')
            ->assertDontSee('Verified customer');

        $this->post('/en/feedback', $this->payload([
            'type' => 'complaint',
            'name' => 'Private Caller',
            'email' => 'private@example.com',
            'phone' => '09120000000',
            'title' => 'Late transfer',
            'message' => 'The car arrived an hour late.',
            'wants_follow_up' => '1',
        ]))->assertSessionHas('sent', 'Your complaint has been stored privately. It will not be published.');

        $complaint = FeedbackSubmission::query()->where('type', 'complaint')->firstOrFail();
        $this->assertNull($complaint->review);
        $this->assertSame('new', $complaint->followUps()->first()->complaint_status);
        $this->assertSame('private@example.com', $complaint->email);

        $this->actingAs($editor)->put('/admin/feedback/'.$complaint->id, [
            'status' => 'approved',
            'complaint_status' => 'reviewing',
            'note' => 'Internal only.',
            'response' => 'We recorded a reply for the team.',
        ])->assertRedirect();

        $this->assertNull($complaint->refresh()->review);
        $this->assertSame('reviewing', $complaint->followUps()->reorder()->latest('id')->value('complaint_status'));

        $this->get('/en/feedback')
            ->assertDontSee('Late transfer')
            ->assertDontSee('private@example.com')
            ->assertDontSee('09120000000')
            ->assertDontSee('Internal only.')
            ->assertDontSee('We recorded a reply for the team.')
            ->assertDontSee('Private Caller');

        app()->setLocale('fa');
        $this->actingAs($editor)->get('/admin/feedback/'.$complaint->id)
            ->assertOk()
            ->assertSee('private@example.com')
            ->assertSee('Internal only.')
            ->assertSee('در حال بررسی')
            ->assertSee('این پاسخ برای تیم ذخیره می‌شود و از این صفحه ایمیل نمی‌شود.');

        $this->actingAs($editor)->get('/admin/feedback?complaint=reviewing')
            ->assertOk()
            ->assertSee('Late transfer')
            ->assertDontSee('Hidden name note');

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->delete('/admin/feedback/'.$complaint->id)->assertRedirect('/admin/feedback');
        $this->assertNull(FeedbackSubmission::query()->find($complaint->id));
    }

    public function test_feedback_validation_honeypot_and_filters(): void
    {
        $this->from('/en/feedback')->post('/en/feedback', $this->payload([
            'name' => '',
            'title' => 'Missing name',
        ]))->assertRedirect('/en/feedback')->assertSessionHasErrors('name');

        $this->from('/en/feedback')->post('/en/feedback', $this->payload([
            'name' => 'Nima',
            'wants_follow_up' => '1',
            'title' => 'Needs contact',
        ]))->assertSessionHasErrors('email');

        $draft = Tour::query()->create(['scope' => 'domestic', 'is_published' => false, 'position' => 1]);
        $this->from('/en/feedback')->post('/en/feedback', $this->payload([
            'name' => 'Nima',
            'title' => 'Unpublished tour',
            'tour_id' => $draft->id,
        ]))->assertSessionHasErrors('tour_id');

        $this->post('/en/feedback', $this->payload([
            'type' => 'complaint',
            'title' => 'No contact needed',
            'message' => 'A private note without a name.',
        ]))->assertSessionHasNoErrors();
        $this->assertSame(1, FeedbackSubmission::query()->count());

        $this->post('/en/feedback', $this->payload([
            'name' => 'Bot',
            'title' => 'Spam note',
            'company_website' => 'https://spam.example',
        ]))->assertRedirect('/en/feedback');
        $this->assertDatabaseCount('feedback_submissions', 1);
    }

    public function test_public_review_filters_keep_a_clean_canonical_url(): void
    {
        $tour = $this->tour();
        $this->post('/en/feedback', $this->payload([
            'type' => 'compliment',
            'name' => 'Laleh',
            'title' => 'Kind guide',
            'message' => 'The guide waited.',
            'rating_overall' => 5,
            'tour_id' => $tour->id,
        ]));
        $compliment = FeedbackSubmission::query()->where('title', 'Kind guide')->firstOrFail();
        $compliment->review->update(['status' => 'approved', 'published_at' => now()]);

        $this->get('/en/feedback?type=compliment')
            ->assertOk()
            ->assertSee('Kind guide')
            ->assertSee('noindex, follow', false)
            ->assertSee('rel="canonical" href="'.route('feedback.index', ['locale' => 'en']).'"', false);

        $this->get('/en/feedback?type=complaint')->assertRedirect(route('feedback.index', ['locale' => 'en']));
        $this->get('/en/feedback?rating=5&tour='.$tour->id)->assertOk()->assertSee('Kind guide');
        $this->get('/en/feedback?rating=9')->assertRedirect(route('feedback.index', ['locale' => 'en']));
    }

    public function test_feedback_submission_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/en/feedback', ['type' => 'general'])->assertRedirect();
        }

        $this->post('/en/feedback', ['type' => 'general'])->assertStatus(429);
        $this->assertDatabaseCount('feedback_submissions', 0);
    }

    public function test_published_reviews_paginate_and_the_sitemap_lists_the_feedback_page(): void
    {
        foreach (range(1, 10) as $n) {
            $submission = FeedbackSubmission::query()->create([
                'type' => 'general',
                'is_anonymous' => true,
                'title' => 'Note '.$n,
                'message' => 'Body '.$n,
                'locale' => 'en',
                'preferred_language' => 'en',
                'consent' => true,
            ]);
            $submission->review()->create([
                'status' => 'approved',
                'published_at' => now(),
            ]);
        }

        $this->get('/en/feedback?page=2')
            ->assertOk()
            ->assertSee('page=2', false)
            ->assertSee('<h3>Note 1</h3>', false)
            ->assertDontSee('<h3>Note 10</h3>', false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/en/feedback')
            ->assertSee('/fa/feedback')
            ->assertSee('/ru/feedback')
            ->assertSee('/ar/feedback')
            ->assertDontSee('feedback/surveys')
            ->assertDontSee('/admin');
    }

    public function test_an_anonymous_survey_can_be_answered_once_and_summarized(): void
    {
        $editor = User::factory()->create();
        $this->actingAs($editor)->post('/admin/surveys', $this->surveyPayload(true))->assertRedirect();

        $survey = Survey::query()->firstOrFail();
        $this->assertTrue($survey->is_anonymous);
        $this->get('/en/feedback')->assertSee('Trip survey')->assertSee('This survey is anonymous.');
        $this->get('/fa/feedback/surveys/'.$survey->id)
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('نظرسنجی سفر')
            ->assertSee('Would you return?')
            ->assertSee('noindex, follow', false)
            ->assertDontSee('name="email"', false);

        $questions = $survey->questions()->with('options')->orderBy('position')->get();
        $answers = [
            $questions[0]->id => $questions[0]->options[0]->id,
            $questions[1]->id => 4,
            $questions[2]->id => 'Quiet lanes',
            $questions[3]->id => [$questions[3]->options[0]->id, $questions[3]->options[1]->id],
        ];

        $this->post('/en/feedback/surveys/'.$survey->id, [
            'name' => 'Should Not Store',
            'email' => 'hidden@example.com',
            'answers' => $answers,
        ])->assertRedirect()->assertSessionHas('sent', 'Your answers have been stored.');

        $stored = SurveyResponse::query()->firstOrFail();
        $this->assertNull($stored->name);
        $this->assertNull($stored->email);
        $this->assertSame(5, $stored->answers()->count());

        $this->post('/en/feedback/surveys/'.$survey->id, ['answers' => $answers])
            ->assertSessionHas('sent', 'This browser already sent answers for this survey.');
        $this->assertSame(1, SurveyResponse::query()->count());

        $this->flushSession();
        $this->from('/en/feedback/surveys/'.$survey->id)->post('/en/feedback/surveys/'.$survey->id, [
            'answers' => [$questions[1]->id => 9],
        ])->assertSessionHasErrors('answers.'.$questions[1]->id);
        $this->assertSame(1, SurveyResponse::query()->count());

        $this->withCookie('survey_'.$survey->id, '1')
            ->post('/en/feedback/surveys/'.$survey->id, ['answers' => $answers])
            ->assertSessionHas('sent', 'This browser already sent answers for this survey.');
        $this->assertSame(1, SurveyResponse::query()->count());

        app()->setLocale('fa');
        $this->actingAs($editor)->get('/admin/surveys/'.$survey->id.'/results')
            ->assertOk()
            ->assertSee('Yes')
            ->assertSee('Quiet lanes')
            ->assertSee('این نظرسنجی ناشناس است');

        $csv = $this->actingAs($editor)->get('/admin/surveys/'.$survey->id.'/export')->assertOk()->streamedContent();
        $this->assertStringContainsString('Would you return?', $csv);
        $this->assertStringContainsString('Quiet lanes', $csv);
        $this->assertStringContainsString('Food; Walks', $csv);
        $this->assertStringNotContainsString('hidden@example.com', $csv);
        $this->assertStringNotContainsString('email', strtolower(strtok($csv, "\n")));

        $this->actingAs($editor)->put('/admin/surveys/'.$survey->id, [
            'is_active' => '1',
            'is_anonymous' => '0',
            'position' => 1,
            'translations' => ['en' => ['title' => 'Trip survey', 'description' => 'Tell us about the trip.']],
        ])->assertRedirect();
        $this->assertTrue($survey->refresh()->is_anonymous);
        $this->assertSame(4, $survey->questions()->count());

        $this->actingAs($editor)->delete('/admin/surveys/'.$survey->id)->assertForbidden();
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->delete('/admin/surveys/'.$survey->id)->assertRedirect('/admin/surveys');
        $this->assertSame(0, Survey::query()->count());
    }

    public function test_an_identified_survey_stores_contact_details_and_rejects_bad_or_repeated_answers(): void
    {
        $editor = User::factory()->create();
        $this->actingAs($editor)->from('/admin/surveys/create')->post('/admin/surveys', [
            'is_active' => '1',
            'is_anonymous' => '0',
            'translations' => ['en' => ['title' => '']],
            'questions' => [],
        ])->assertSessionHasErrors('is_active');

        $this->actingAs($editor)->post('/admin/surveys', [
            'is_active' => '1',
            'is_anonymous' => '0',
            'opens_at' => '2026-10-12',
            'closes_at' => '2026-10-01',
            'translations' => ['en' => ['title' => 'Named survey']],
            'questions' => [[
                'type' => 'single',
                'is_required' => '1',
                'translations' => ['en' => ['prompt' => 'City?']],
                'options' => [['translations' => ['en' => ['label' => 'Yazd']]]],
            ]],
        ])->assertSessionHasErrors('closes_at');

        $this->actingAs($editor)->post('/admin/surveys', [
            'is_active' => '1',
            'is_anonymous' => '0',
            'position' => 1,
            'translations' => [
                'en' => ['title' => 'Named survey', 'description' => 'Answers include the name and email you enter.'],
            ],
            'questions' => [[
                'type' => 'single',
                'is_required' => '1',
                'translations' => ['en' => ['prompt' => 'City?']],
                'options' => [
                    ['translations' => ['en' => ['label' => 'Yazd']]],
                    ['translations' => ['en' => ['label' => 'Shiraz']]],
                ],
            ]],
        ])->assertRedirect();
        $survey = Survey::query()->firstOrFail();
        $this->assertFalse($survey->is_anonymous);

        $this->get('/en/feedback/surveys/'.$survey->id)
            ->assertOk()
            ->assertSee('This survey is not anonymous. A name or email you enter is stored with your answers.')
            ->assertSee('name="email"', false);

        $question = $survey->questions()->with('options')->orderBy('position')->firstOrFail();
        $this->from('/en/feedback/surveys/'.$survey->id)->post('/en/feedback/surveys/'.$survey->id, [
            'name' => 'Laleh',
            'email' => 'laleh@example.com',
            'answers' => [$question->id => 99999],
        ])->assertSessionHasErrors('answers.'.$question->id);

        $this->post('/en/feedback/surveys/'.$survey->id, [
            'name' => 'Laleh',
            'email' => 'laleh@example.com',
            'answers' => [$question->id => $question->options[0]->id],
        ])->assertSessionHas('sent', 'Your answers have been stored.');

        $stored = SurveyResponse::query()->firstOrFail();
        $this->assertSame('Laleh', $stored->name);
        $this->assertSame('laleh@example.com', $stored->email);

        $this->flushSession();
        $this->from('/en/feedback/surveys/'.$survey->id)->post('/en/feedback/surveys/'.$survey->id, [
            'name' => 'Laleh',
            'email' => 'laleh@example.com',
            'answers' => [$question->id => $question->options[0]->id],
        ])->assertSessionHasErrors('email');
        $this->assertSame(1, SurveyResponse::query()->count());

        $csv = $this->actingAs($editor)->get('/admin/surveys/'.$survey->id.'/export')->assertOk()->streamedContent();
        $this->assertStringContainsString('laleh@example.com', $csv);

        $this->flushSession();
        $this->post('/en/feedback/surveys/'.$survey->id, [
            'company_website' => 'https://spam.example',
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'answers' => [$question->id => $question->options[0]->id],
        ])->assertRedirect();
        $this->assertSame(1, SurveyResponse::query()->count());

        $survey->update(['closes_at' => '2020-01-01']);
        $this->get('/en/feedback/surveys/'.$survey->id)->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'type' => 'general',
            'name' => 'Ada',
            'title' => 'A note',
            'message' => 'A detailed note about the trip.',
            'preferred_language' => 'en',
            'consent' => '1',
        ];
    }

    private function storeFeedback(): FeedbackSubmission
    {
        $this->post('/en/feedback', $this->payload());

        return FeedbackSubmission::query()->firstOrFail();
    }

    private function tour(): Tour
    {
        $tour = Tour::query()->create([
            'scope' => 'domestic',
            'is_published' => true,
            'position' => 1,
        ]);
        $tour->translations()->create([
            'locale' => 'en',
            'title' => 'Yazd lanes',
            'slug' => 'yazd-lanes',
        ]);

        return $tour;
    }

    /**
     * @return array<string, mixed>
     */
    private function surveyPayload(bool $anonymous): array
    {
        return [
            'is_active' => '1',
            'is_anonymous' => $anonymous ? '1' : '0',
            'position' => 1,
            'translations' => [
                'en' => ['title' => 'Trip survey', 'description' => 'Tell us about the trip.'],
                'fa' => ['title' => 'نظرسنجی سفر', 'description' => 'از سفر بگویید.'],
            ],
            'questions' => [
                [
                    'type' => 'single',
                    'is_required' => '1',
                    'translations' => ['en' => ['prompt' => 'Would you return?']],
                    'options' => [
                        ['translations' => ['en' => ['label' => 'Yes']]],
                        ['translations' => ['en' => ['label' => 'No']]],
                    ],
                ],
                [
                    'type' => 'rating',
                    'is_required' => '1',
                    'translations' => ['en' => ['prompt' => 'Guide score']],
                ],
                [
                    'type' => 'text',
                    'is_required' => '0',
                    'translations' => ['en' => ['prompt' => 'Anything else']],
                ],
                [
                    'type' => 'multiple',
                    'is_required' => '0',
                    'translations' => ['en' => ['prompt' => 'Highlights']],
                    'options' => [
                        ['translations' => ['en' => ['label' => 'Food']]],
                        ['translations' => ['en' => ['label' => 'Walks']]],
                    ],
                ],
            ],
        ];
    }
}
