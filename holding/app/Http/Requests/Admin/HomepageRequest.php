<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use App\Support\VideoEmbed;

class HomepageRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $text = ['nullable', 'string', 'max:180'];
        $long = ['nullable', 'string', 'max:5000'];

        return [
            'video_type' => ['required', 'in:none,file,url'],
            'video_url' => ['nullable', 'string', 'max:255'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4', 'max:40960'],
            'poster' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'intro_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_video' => ['nullable', 'boolean'],
            'remove_poster' => ['nullable', 'boolean'],
            'remove_hero' => ['nullable', 'boolean'],
            'remove_intro' => ['nullable', 'boolean'],
            'translations' => ['nullable', 'array'],
            'translations.*.hero_eyebrow' => $text,
            'translations.*.hero_title' => $text,
            'translations.*.hero_lede' => $long,
            'translations.*.intro_heading' => $text,
            'translations.*.intro_body' => $long,
            'translations.*.divisions_heading' => $text,
            'translations.*.approach_heading' => $text,
            'translations.*.approach_body' => $long,
            'translations.*.projects_heading' => $text,
            'translations.*.cta_heading' => $text,
            'translations.*.cta_text' => $long,
            'translations.*.meta_title' => $text,
            'translations.*.meta_description' => ['nullable', 'string', 'max:320'],
            'translations.*.canonical_url' => $this->canonicalRule(),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('video_type') === 'url' && VideoEmbed::fromUrl($this->input('video_url')) === null) {
                $validator->errors()->add('video_url', __('admin.validation.video'));
            }

            if ($this->input('video_type') === 'file' && ! $this->hasFile('video') && ! filled(Setting::getValue('video_path'))) {
                $validator->errors()->add('video', __('admin.validation.video_file'));
            }
        });
    }
}
