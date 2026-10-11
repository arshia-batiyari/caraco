<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DivisionRequest;
use App\Http\Requests\Admin\ReorderDivisionsRequest;
use App\Models\Division;
use App\Support\LocaleText;
use App\Support\MediaUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class DivisionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Division::class);

        return view('admin.divisions.index', [
            'divisions' => \App\Support\AdminList::paginate(Division::query()->with('translations'), request(), 'title'),
        ]);
    }

    public function reorder(ReorderDivisionsRequest $request): RedirectResponse
    {
        $this->authorize('viewAny', Division::class);

        DB::transaction(function () use ($request) {
            foreach ($request->input('divisions', []) as $id => $row) {
                if (! is_array($row)) {
                    continue;
                }
                Division::query()->whereKey($id)->update([
                    'position' => (int) $row['position'],
                    'is_published' => $request->boolean('divisions.'.$id.'.is_published'),
                ]);
            }
        });

        return redirect()->route('admin.divisions.index')->with('status', __('admin.saved'));
    }

    public function edit(Division $division): View
    {
        $this->authorize('update', $division);

        return view('admin.divisions.edit', [
            'division' => $division->load('translations'),
        ]);
    }

    public function update(DivisionRequest $request, Division $division): RedirectResponse
    {
        $this->authorize('update', $division);

        try {
            $image = MediaUploads::image(
                $division->image_path,
                $request->file('image'),
                $request->boolean('remove_image'),
                'divisions'
            );
        } catch (RuntimeException) {
            return back()->withInput()->withErrors(['image' => __('admin.validation.image')]);
        }

        DB::transaction(function () use ($request, $division, $image) {
            $division->update([
                'position' => $request->integer('position'),
                'is_published' => $request->boolean('is_published'),
                'destination_url' => LocaleText::blank($request->input('destination_url')),
                'image_path' => $image,
            ]);

            LocaleText::sync($division, $request->input('translations', []), [
                'title',
                'slug',
                'introduction',
                'description',
                'meta_title',
                'meta_description',
                'canonical_url',
            ], 'title');
        });

        return redirect()->route('admin.divisions.edit', $division)->with('status', __('admin.saved'));
    }
}
