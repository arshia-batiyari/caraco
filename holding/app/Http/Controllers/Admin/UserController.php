<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'users' => \App\Support\AdminList::paginate(User::query(), request()),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.form', [
            'account' => new User(['role' => 'editor']),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::query()->create($request->safe()->only(['name', 'email', 'password', 'role']));

        return redirect()->route('admin.users.edit', $user)->with('status', __('admin.saved'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.form', [
            'account' => $user,
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        if ($user->isAdmin() && $request->input('role') !== 'admin' && User::query()->where('role', 'admin')->count() === 1) {
            return back()->withInput()->withErrors(['role' => __('admin.validation.last_admin')]);
        }

        $data = $request->safe()->only(['name', 'email', 'role']);
        if (filled($request->input('password'))) {
            $data['password'] = $request->input('password');
        }
        $user->update($data);

        return redirect()->route('admin.users.edit', $user)->with('status', __('admin.saved'));
    }
}
