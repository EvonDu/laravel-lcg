<?php

namespace App\Expand\Lcg\Auth\Controllers;

use App\Expand\Lcg\Auth\Contracts\LoginViewResponse;
use App\Expand\Lcg\Auth\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Expand\Lcg\Auth\Contracts\LoginViewResponse
     */
    public function create(Request $request)
    {
        return app(LoginViewResponse::class);

        /*return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);*/
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Expand\Lcg\Auth\Requests\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
