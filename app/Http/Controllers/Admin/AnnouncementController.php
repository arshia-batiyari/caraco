<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->paginate(20);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        $announcement = new Announcement(['type' => 'info', 'priority' => 0, 'is_active' => true]);
        $announcement->title = [];
        $announcement->message = [];

        return view('admin.announcements.create', compact('announcement'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        Announcement::create($data);

        return redirect()->route('admin.announcements.index')->with('success', 'اگهی جدید ثبت شد.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->validated($request));

        return redirect()->route('admin.announcements.index')->with('success', 'اگهی به‌روزرسانی شد.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('success', 'اگهی حذف شد.');
    }

    /** فعال/غیرفعال کردن سریع، بدون باز کردن فرم ویرایش */
    public function toggle(Announcement $announcement)
    {
        $announcement->update(['is_active' => ! $announcement->is_active]);

        return back()->with('success', $announcement->is_active ? 'اگهی فعال شد.' : 'اگهی غیرفعال شد.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title_fa' => 'required|string|max:150',
            'title_en' => 'nullable|string|max:150',
            'message_fa' => 'nullable|string|max:500',
            'message_en' => 'nullable|string|max:500',
            'type' => 'required|in:info,offer,urgent',
            'link_url' => 'nullable|url|max:255',
            'link_text' => 'nullable|string|max:40',
            'priority' => 'nullable|integer|min:0|max:255',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        return [
            'title' => ['fa' => $data['title_fa'], 'en' => $data['title_en'] ?: $data['title_fa']],
            'message' => $data['message_fa'] ? ['fa' => $data['message_fa'], 'en' => $data['message_en'] ?: $data['message_fa']] : null,
            'type' => $data['type'],
            'link_url' => $data['link_url'] ?? null,
            'link_text' => $data['link_text'] ?? null,
            'priority' => $data['priority'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ];
    }
}