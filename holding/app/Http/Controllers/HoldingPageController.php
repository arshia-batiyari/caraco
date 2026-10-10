<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class HoldingPageController extends Controller
{
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
}
