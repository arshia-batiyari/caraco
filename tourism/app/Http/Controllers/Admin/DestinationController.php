<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DestinationRequest;
use App\Models\Destination;
use App\Support\CatalogCache;
use App\Support\ImageStore;
use App\Support\LocaleText;
use App\Support\MediaUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class DestinationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Destination::class);

        return view('admin.destinations.index', [
            'records' => Destination::query()->with('translations', 'parent.translations')->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Destination::class);

        return view('admin.destinations.form', [
            'record' => new Destination(['type' => 'city', 'position' => 0, 'is_published' => false]),
            'parents' => Destination::query()->with('translations')->orderBy('position')->get(),
        ]);
    }

    public function store(DestinationRequest $request): RedirectResponse
    {
        $this->authorize('create', Destination::class);

        try {
            $image = MediaUploads::image(null, $request->file('image'), false, 'destinations');
        } catch (RuntimeException) {
            return back()->withInput()->withErrors(['image' => __('admin.validation.image')]);
        }

        $record = Destination::query()->create([
            'parent_id' => $request->input('parent_id') ?: null,
            'type' => $request->input('type'),
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'position' => $request->integer('position'),
            'image_path' => $image,
        ]);
        $this->sync($request, $record);
        CatalogCache::flush();

        return redirect()->route('admin.destinations.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(Destination $destination): View
    {
        $this->authorize('update', $destination);

        return view('admin.destinations.form', [
            'record' => $destination->load('translations'),
            'parents' => Destination::query()->where('id', '!=', $destination->id)->with('translations')->orderBy('position')->get(),
        ]);
    }

    public function update(DestinationRequest $request, Destination $destination): RedirectResponse
    {
        $this->authorize('update', $destination);
        if ((int) $request->input('parent_id') === $destination->id) {
            return back()->withInput()->withErrors(['parent_id' => __('admin.validation.parent')]);
        }

        try {
            $image = MediaUploads::image($destination->image_path, $request->file('image'), $request->boolean('remove_image'), 'destinations');
        } catch (RuntimeException) {
            return back()->withInput()->withErrors(['image' => __('admin.validation.image')]);
        }

        $destination->update([
            'parent_id' => $request->input('parent_id') ?: null,
            'type' => $request->input('type'),
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'position' => $request->integer('position'),
            'image_path' => $image,
        ]);
        $this->sync($request, $destination);
        CatalogCache::flush();

        return redirect()->route('admin.destinations.edit', $destination)->with('status', __('admin.saved'));
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        $this->authorize('delete', $destination);
        ImageStore::delete($destination->image_path);
        $destination->delete();
        CatalogCache::flush();

        return redirect()->route('admin.destinations.index')->with('status', __('admin.saved'));
    }

    private function sync(DestinationRequest $request, Destination $destination): void
    {
        LocaleText::sync($destination, $request->input('translations', []), [
            'name', 'slug', 'summary', 'description', 'meta_title', 'meta_description', 'canonical_url',
        ], 'name');
    }
}
