<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FeedbackUpdateRequest;
use App\Models\FeedbackSubmission;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FeedbackSubmission::class);

        $query = FeedbackSubmission::query()->with(['review', 'followUps'])->latest();
        if (in_array($request->query('type'), ['general', 'complaint', 'suggestion', 'compliment'], true)) {
            $query->where('type', $request->query('type'));
        }
        if (in_array($request->query('status'), ['pending', 'approved', 'rejected', 'hidden'], true)) {
            $query->whereHas('review', fn ($inner) => $inner->where('status', $request->query('status')));
        }
        if (in_array($request->query('complaint'), ['new', 'reviewing', 'awaiting', 'resolved', 'closed'], true)) {
            $status = $request->query('complaint');
            $latest = DB::table('feedback_follow_ups')
                ->selectRaw('max(id) as id')
                ->groupBy('feedback_submission_id');
            $query->where('type', 'complaint')->whereIn('id', function ($inner) use ($status, $latest) {
                $inner->select('feedback_follow_ups.feedback_submission_id')
                    ->from('feedback_follow_ups')
                    ->joinSub($latest, 'latest_follow_ups', 'latest_follow_ups.id', '=', 'feedback_follow_ups.id')
                    ->where('feedback_follow_ups.complaint_status', $status);
            });
        }

        return view('admin.feedback.index', [
            'records' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['type', 'status', 'complaint']),
        ]);
    }

    public function show(FeedbackSubmission $submission): View
    {
        $this->authorize('view', $submission);

        return view('admin.feedback.show', [
            'submission' => $submission->load(['review', 'tour.translations', 'service.translations', 'followUps.author']),
        ]);
    }

    public function update(FeedbackUpdateRequest $request, FeedbackSubmission $submission): RedirectResponse
    {
        $this->authorize('update', $submission);

        DB::transaction(function () use ($request, $submission) {
            $review = $submission->review;
            if ($review && $submission->canBePublic() && filled($request->input('status'))) {
                $status = $request->input('status');
                $verified = $request->boolean('is_verified');
                $review->update([
                    'status' => $status,
                    'is_verified' => $verified,
                    'verified_by' => $verified ? $request->user()->id : null,
                    'moderated_by' => $request->user()->id,
                    'published_at' => $status === 'approved' ? ($review->published_at ?? now()) : null,
                ]);
            }

            if (filled($request->input('complaint_status')) || filled($request->input('note')) || filled($request->input('response'))) {
                $submission->followUps()->create([
                    'user_id' => $request->user()->id,
                    'complaint_status' => $request->input('complaint_status'),
                    'note' => LocaleText::blank($request->input('note')),
                    'response' => LocaleText::blank($request->input('response')),
                ]);
            }
        });

        return redirect()->route('admin.feedback.show', $submission)->with('status', __('admin.saved'));
    }

    public function destroy(FeedbackSubmission $submission): RedirectResponse
    {
        $this->authorize('delete', $submission);
        $submission->delete();

        return redirect()->route('admin.feedback.index')->with('status', __('admin.saved'));
    }
}
