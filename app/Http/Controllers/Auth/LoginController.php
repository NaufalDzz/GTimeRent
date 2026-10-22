<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.login', [
            'tab' => $request->query('tab') === 'daftar' || session()->has('errors') && session('errors')->hasBag('register') ? 'daftar' : 'masuk',
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $archived = User::onlyTrashed()->where('email', $credentials['email'])->exists();

            throw ValidationException::withMessages([
                'email' => $archived
                    ? 'Akun ini sudah diarsipkan admin dan tidak bisa masuk. Hubungi admin GTR jika ini keliru.'
                    : 'Email atau password tidak cocok.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->isAdmin() ? route('admin.rentals.index') : route('catalog.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
