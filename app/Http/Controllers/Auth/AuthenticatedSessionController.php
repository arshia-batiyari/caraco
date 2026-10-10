<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'ایمیل یا رمز عبور اشتباه است.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPathForRole());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /** بعد از لاگین، هر نقش را به پنل مخصوص خودش هدایت می‌کند */
    protected function redirectPathForRole(): string
    {
        return match (Auth::user()->role) {
            'admin'  => route('admin.dashboard'),
            'vendor' => Auth::user()->booth?->status === 'approved'
                ? route('vendor.dashboard')
                : route('vendor.pending'),
            'landowner' => route('landowner.dashboard'),
            default  => route('home'),
        };
    }
}
