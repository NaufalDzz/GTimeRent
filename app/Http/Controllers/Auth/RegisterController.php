<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /**
     * Register publik selalu membuat anggota; role dari request diabaikan.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $user = new User($request->only('name', 'email', 'password'));
            $user->role = User::ROLE_MEMBER;
            $user->save();
            $user->profile()->create();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('profile.edit')
            ->with('status', 'Akun dibuat. Lengkapi telepon dan alamat supaya checkout lebih cepat.');
    }
}
