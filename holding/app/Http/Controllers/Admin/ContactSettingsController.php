<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactSettingsRequest;
use App\Models\ContactTranslation;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Support\LocaleRows;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.contact', [
            'email' => Setting::getValue('contact_email', ''),
            'phone' => Setting::getValue('contact_phone', ''),
            'translations' => ContactTranslation::query()->get()->keyBy('locale'),
            'socialLinks' => SocialLink::query()->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function update(ContactSettingsRequest $request): RedirectResponse
    {
        Setting::putValue('contact_email', LocaleText::blank($request->input('contact_email')));
        Setting::putValue('contact_phone', LocaleText::blank($request->input('contact_phone')));
        LocaleRows::sync(ContactTranslation::class, $request->input('translations', []), ['address', 'hours', 'note']);

        $remove = array_map('intval', $request->input('remove_social', []));
        if ($remove !== []) {
            SocialLink::query()->whereIn('id', $remove)->delete();
        }

        foreach ($request->input('social', []) as $id => $row) {
            if (! is_array($row) || in_array((int) $id, $remove, true)) {
                continue;
            }
            SocialLink::query()->whereKey($id)->update([
                'platform' => $row['platform'],
                'url' => trim((string) $row['url']),
                'position' => (int) ($row['position'] ?? 0),
                'is_published' => $request->boolean('social.'.$id.'.is_published'),
            ]);
        }

        $new = $request->input('new_social', []);
        if (is_array($new) && filled($new['platform'] ?? null) && filled($new['url'] ?? null)) {
            SocialLink::query()->create([
                'platform' => $new['platform'],
                'url' => trim((string) $new['url']),
                'position' => (int) ($new['position'] ?? 0),
                'is_published' => true,
            ]);
        }

        return redirect()->route('admin.contact.edit')->with('status', __('admin.saved'));
    }
}
