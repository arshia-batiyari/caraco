<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomepageRequest;
use App\Models\HomepageTranslation;
use App\Models\Setting;
use App\Support\LocaleText;
use App\Support\MediaUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class HomepageController extends Controller
{
    /**
     * @var list<string>
     */
    private array $fields = [
        'hero_eyebrow',
        'hero_title',
        'hero_lede',
        'intro_heading',
        'intro_body',
        'divisions_heading',
        'approach_heading',
        'approach_body',
        'projects_heading',
        'cta_heading',
        'cta_text',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];

    public function edit(): View
    {
        return view('admin.homepage', [
            'translations' => HomepageTranslation::query()->get()->keyBy('locale'),
            'videoType' => Setting::getValue('video_type', 'none'),
            'videoUrl' => Setting::getValue('video_url', ''),
            'videoPath' => Setting::getValue('video_path'),
            'posterPath' => Setting::getValue('poster_path'),
            'heroImage' => Setting::getValue('hero_image'),
            'introImage' => Setting::getValue('intro_image'),
        ]);
    }

    public function update(HomepageRequest $request): RedirectResponse
    {
        try {
            $hero = MediaUploads::image(Setting::getValue('hero_image'), $request->file('hero_image'), $request->boolean('remove_hero'), 'heroes');
            $intro = MediaUploads::image(Setting::getValue('intro_image'), $request->file('intro_image'), $request->boolean('remove_intro'), 'intro');
            $poster = MediaUploads::image(Setting::getValue('poster_path'), $request->file('poster'), $request->boolean('remove_poster'), 'posters');
            $video = MediaUploads::video(Setting::getValue('video_path'), $request->file('video'), $request->boolean('remove_video'));
        } catch (RuntimeException) {
            return back()->withInput()->withErrors(['hero_image' => __('admin.validation.image')]);
        }

        Setting::putValue('hero_image', $hero);
        Setting::putValue('intro_image', $intro);
        Setting::putValue('poster_path', $poster);
        Setting::putValue('video_path', $video);
        Setting::putValue('video_type', (string) $request->input('video_type'));
        Setting::putValue('video_url', LocaleText::blank($request->input('video_url')));

        $input = $request->input('translations', []);
        foreach (array_keys(config('karako.locales')) as $locale) {
            $row = is_array($input[$locale] ?? null) ? $input[$locale] : [];
            $payload = [];
            $blank = true;
            foreach ($this->fields as $field) {
                $payload[$field] = LocaleText::blank(isset($row[$field]) ? (string) $row[$field] : null);
                if ($payload[$field] !== null) {
                    $blank = false;
                }
            }

            $existing = HomepageTranslation::query()->where('locale', $locale)->first();
            if ($blank) {
                $existing?->delete();

                continue;
            }

            HomepageTranslation::query()->updateOrCreate(['locale' => $locale], $payload);
        }

        return redirect()->route('admin.homepage.edit')->with('status', __('admin.saved'));
    }
}
