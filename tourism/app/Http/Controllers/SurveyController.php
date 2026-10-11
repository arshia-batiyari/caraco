<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyResponseRequest;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Support\ContentPresenter;
use App\Support\LocaleText;
use App\Support\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SurveyController extends Controller
{
    public function show(ContentPresenter $presenter): View
    {
        $survey = $this->survey();
        $survey->load(['translations', 'questions.translations', 'questions.options.translations']);
        abort_unless($this->isOpen($survey), 404);
        $done = $this->alreadySubmitted($survey);

        return $presenter->render('pages.feedback.survey', [
            'survey' => $survey,
            'done' => $done,
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => __('ui.feedback.title'), 'url' => route('feedback.index')],
                ['label' => $survey->publicTranslation()?->title ?? __('ui.survey.title'), 'url' => route('feedback.surveys.show', $survey)],
            ],
        ], new SeoData(
            title: $presenter->documentTitle(null, (string) ($survey->publicTranslation()?->title ?? __('ui.survey.title'))),
            description: (string) ($survey->publicTranslation()?->description ?? ''),
            canonical: route('feedback.surveys.show', $survey),
            robots: 'noindex, follow',
        ));
    }

    public function store(StoreSurveyResponseRequest $request): RedirectResponse
    {
        $survey = $this->survey();
        abort_unless($this->isOpen($survey), 404);
        if ($request->honeypotFilled() || $this->alreadySubmitted($survey)) {
            return redirect()->route('feedback.surveys.show', $survey)->with('sent', __('ui.survey.already'));
        }

        $survey->load('questions.options');
        if (! $survey->is_anonymous && filled($request->input('email'))) {
            $duplicate = SurveyResponse::query()
                ->where('survey_id', $survey->id)
                ->where('email', $request->input('email'))
                ->exists();
            if ($duplicate) {
                return back()->withInput()->withErrors(['email' => __('ui.survey.validation.duplicate')]);
            }
        }

        DB::transaction(function () use ($request, $survey) {
            $response = $survey->responses()->create([
                'locale' => app()->getLocale(),
                'name' => $survey->is_anonymous ? null : LocaleText::blank($request->input('name')),
                'email' => $survey->is_anonymous ? null : LocaleText::blank($request->input('email')),
            ]);

            foreach ($survey->questions as $question) {
                $this->storeAnswers($response, $question, $request->input('answers.'.$question->id));
            }
        });

        $request->session()->put($this->sessionKey($survey), true);

        return redirect()
            ->route('feedback.surveys.show', $survey)
            ->with('sent', __('ui.survey.stored'))
            ->withCookie(cookie($this->cookieName($survey), '1', 60 * 24 * 180));
    }

    private function survey(): Survey
    {
        $parameter = request()->route('survey');
        if ($parameter instanceof Survey) {
            return $parameter;
        }

        $survey = Survey::query()->find($parameter);
        abort_unless($survey instanceof Survey, 404);

        return $survey;
    }

    private function isOpen(Survey $survey): bool
    {
        if (! $survey->is_active || $survey->publicTranslation() === null) {
            return false;
        }
        $today = now()->startOfDay();
        if ($survey->opens_at && $today->lt($survey->opens_at->copy()->startOfDay())) {
            return false;
        }
        if ($survey->closes_at && $today->gt($survey->closes_at->copy()->startOfDay())) {
            return false;
        }

        return true;
    }

    private function alreadySubmitted(Survey $survey): bool
    {
        return session($this->sessionKey($survey)) === true
            || request()->cookie($this->cookieName($survey)) === '1';
    }

    private function sessionKey(Survey $survey): string
    {
        return 'survey_done.'.$survey->id;
    }

    private function cookieName(Survey $survey): string
    {
        return 'survey_'.$survey->id;
    }

    private function storeAnswers(SurveyResponse $response, SurveyQuestion $question, mixed $value): void
    {
        if ($value === null || $value === '' || $value === []) {
            return;
        }

        if ($question->type === 'multiple') {
            foreach ((array) $value as $choice) {
                $response->answers()->create([
                    'survey_question_id' => $question->id,
                    'survey_option_id' => (int) $choice,
                ]);
            }

            return;
        }

        if ($question->type === 'single') {
            $response->answers()->create([
                'survey_question_id' => $question->id,
                'survey_option_id' => (int) $value,
            ]);

            return;
        }

        if ($question->type === 'rating') {
            $response->answers()->create([
                'survey_question_id' => $question->id,
                'rating_value' => (int) $value,
            ]);

            return;
        }

        $response->answers()->create([
            'survey_question_id' => $question->id,
            'text_value' => (string) $value,
        ]);
    }
}
