<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class TourismPageController extends Controller
{
    public function home(): View
    {
        return view('pages.home');
    }

    public function journey(): View
    {
        return view('pages.journey', [
            'days' => __('pages.journey.days'),
        ]);
    }

    public function inquiry(): View
    {
        return view('pages.message', [
            'copy' => __('pages.inquiry'),
            'action' => route('inquiry.store'),
        ]);
    }

    public function storeInquiry(Request $request): RedirectResponse
    {
        $request->validate($this->rules(), $this->messages());

        return redirect()->route('inquiry')->with('sent', true);
    }

    public function foundations(): View
    {
        $items = collect(range(1, 12))->map(fn (int $number) => [
            'title' => __('ui.samples.entry', ['n' => $number]),
        ]);

        $perPage = 4;
        $page = LengthAwarePaginator::resolveCurrentPage();

        return view('pages.foundations', [
            'entries' => new LengthAwarePaginator(
                $items->forPage($page, $perPage)->values(),
                $items->count(),
                $perPage,
                $page,
                ['path' => route('foundations')]
            ),
        ]);
    }

    public function credits(): View
    {
        return view('pages.credits');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'name.required' => __('ui.validation.required', ['field' => __('ui.fields.name')]),
            'name.max' => __('ui.validation.max', ['field' => __('ui.fields.name')]),
            'email.required' => __('ui.validation.required', ['field' => __('ui.fields.email')]),
            'email.email' => __('ui.validation.email'),
            'email.max' => __('ui.validation.max', ['field' => __('ui.fields.email')]),
            'message.required' => __('ui.validation.required', ['field' => __('ui.fields.message')]),
            'message.max' => __('ui.validation.max', ['field' => __('ui.fields.message')]),
        ];
    }
}
