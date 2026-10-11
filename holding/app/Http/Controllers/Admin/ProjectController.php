<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use App\Support\ImageStore;
use App\Support\LocaleText;
use App\Support\MediaUploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class ProjectController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Project::class);

        return view('admin.projects.index', [
            'projects' => \App\Support\AdminList::paginate(Project::query()->with('translations'), request(), 'title'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('admin.projects.form', [
            'project' => new Project(['position' => 0, 'is_published' => false]),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        try {
            $image = MediaUploads::image(null, $request->file('image'), false, 'projects');
        } catch (RuntimeException) {
            return back()->withInput()->withErrors(['image' => __('admin.validation.image')]);
        }

        $project = DB::transaction(function () use ($request, $image) {
            $project = Project::query()->create([
                'position' => $request->integer('position'),
                'is_published' => $request->boolean('is_published'),
                'image_path' => $image,
            ]);
            $this->sync($request, $project);

            return $project;
        });

        return redirect()->route('admin.projects.edit', $project)->with('status', __('admin.saved'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('admin.projects.form', [
            'project' => $project->load('translations'),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        try {
            $image = MediaUploads::image(
                $project->image_path,
                $request->file('image'),
                $request->boolean('remove_image'),
                'projects'
            );
        } catch (RuntimeException) {
            return back()->withInput()->withErrors(['image' => __('admin.validation.image')]);
        }

        DB::transaction(function () use ($request, $project, $image) {
            $project->update([
                'position' => $request->integer('position'),
                'is_published' => $request->boolean('is_published'),
                'image_path' => $image,
            ]);
            $this->sync($request, $project);
        });

        return redirect()->route('admin.projects.edit', $project)->with('status', __('admin.saved'));
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);
        $this->requireDeleteConfirmation();
        ImageStore::delete($project->image_path);
        $project->delete();

        return redirect()->route('admin.projects.index')->with('status', __('admin.saved'));
    }

    private function sync(ProjectRequest $request, Project $project): void
    {
        LocaleText::sync($project, $request->input('translations', []), [
            'title',
            'slug',
            'summary',
            'body',
            'meta_title',
            'meta_description',
            'canonical_url',
        ], 'title');
    }
}
