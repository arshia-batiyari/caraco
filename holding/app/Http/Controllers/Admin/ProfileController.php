<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileRequest;
use App\Models\ProfileTranslation;
use App\Models\Setting;
use App\Support\LocaleRows;
use App\Support\LocaleText;
use App\Support\MediaUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile', [
            'companyName' => Setting::getValue('company_name', ''),
            'translations' => ProfileTranslation::query()->get()->keyBy('locale'),
            'ogImage' => Setting::getValue('og_image'),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        try {
            $path = MediaUploads::image(
                Setting::getValue('og_image'),
                $request->file('og_image'),
                $request->boolean('remove_og'),
                'profile'
            );
        } catch (RuntimeException) {
            return back()->withInput()->withErrors(['og_image' => __('admin.validation.image')]);
        }

        Setting::putValue('company_name', LocaleText::blank($request->input('company_name')));
        Setting::putValue('og_image', $path);
        LocaleRows::sync(ProfileTranslation::class, $request->input('translations', []), ['summary']);

        return redirect()->route('admin.profile.edit')->with('status', __('admin.saved'));
    }
}
