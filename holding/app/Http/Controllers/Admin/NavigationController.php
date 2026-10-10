<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NavigationRequest;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NavigationController extends Controller
{
    public function edit(): View
    {
        return view('admin.navigation', [
            'items' => NavigationItem::query()->orderBy('location')->orderBy('position')->with('translations')->get(),
            'pages' => Page::query()->orderBy('position')->with('translations')->get(),
        ]);
    }

    public function update(NavigationRequest $request): RedirectResponse
    {
        $remove = array_map('intval', $request->input('remove', []));
        if ($remove !== []) {
            NavigationItem::query()->whereIn('id', $remove)->delete();
        }

        foreach ($request->input('items', []) as $id => $row) {
            if (! is_array($row) || in_array((int) $id, $remove, true)) {
                continue;
            }
            $item = NavigationItem::query()->find($id);
            if ($item) {
                $this->fill($item, $row);
            }
        }

        $new = $request->input('new', []);
        if (is_array($new) && filled($new['kind'] ?? null)) {
            $this->fill(new NavigationItem, $new);
        }

        return redirect()->route('admin.navigation.edit')->with('status', __('admin.saved'));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function fill(NavigationItem $item, array $row): void
    {
        $kind = (string) $row['kind'];
        $item->fill([
            'location' => $row['location'],
            'kind' => $kind,
            'route_name' => $kind === 'route' ? ($row['route_name'] ?? null) : null,
            'page_id' => $kind === 'page' ? ($row['page_id'] ?? null) : null,
            'url' => $kind === 'url' ? LocaleText::blank(isset($row['url']) ? (string) $row['url'] : null) : null,
            'position' => (int) ($row['position'] ?? 0),
            'is_published' => filter_var($row['is_published'] ?? false, FILTER_VALIDATE_BOOL),
        ]);
        $item->save();

        $labels = is_array($row['labels'] ?? null) ? $row['labels'] : [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $label = LocaleText::blank(isset($labels[$locale]) ? (string) $labels[$locale] : null);
            $existing = $item->translations()->where('locale', $locale)->first();
            if ($label === null) {
                $existing?->delete();

                continue;
            }
            $item->translations()->updateOrCreate(['locale' => $locale], ['label' => $label]);
        }
    }
}
