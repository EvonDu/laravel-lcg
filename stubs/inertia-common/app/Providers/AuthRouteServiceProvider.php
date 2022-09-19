<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Lcg\Contracts\ConfirmPasswordViewResponse;
use Lcg\Contracts\LoginViewResponse;
use Lcg\Contracts\RegisterViewResponse;
use Lcg\Contracts\RequestPasswordResetLinkViewResponse;
use Lcg\Contracts\ResetPasswordViewResponse;
use Lcg\Contracts\VerifyEmailViewResponse;
use Lcg\Exceptions\Inertia\Inertia;
use Lcg\Http\Controllers\Auth\AuthenticatedSessionController;
use Lcg\Http\Controllers\Auth\ConfirmablePasswordController;
use Lcg\Http\Controllers\Auth\EmailVerificationNotificationController;
use Lcg\Http\Controllers\Auth\EmailVerificationPromptController;
use Lcg\Http\Controllers\Auth\NewPasswordController;
use Lcg\Http\Controllers\Auth\PasswordResetLinkController;
use Lcg\Http\Controllers\Auth\RegisteredUserController;
use Lcg\Http\Controllers\Auth\VerifyEmailController;
use Lcg\Http\Responses\SimpleViewResponse;

class AuthRouteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        app()->singleton(LoginViewResponse::class, function () {
            return new SimpleViewResponse(function($request) {
                return Inertia::render('Auth/Login', [
                    'canResetPassword' => Route::has('password.request'),
                    'status' => session('status'),
                ]);
            });
        });

        app()->singleton(ConfirmPasswordViewResponse::class, function ($request) {
            return new SimpleViewResponse(function($request) {
                return Inertia::render('Auth/ConfirmPassword');
            });
        });

        app()->singleton(VerifyEmailViewResponse::class, function () {
            return new SimpleViewResponse(function($request) {
                return Inertia::render('Auth/VerifyEmail', ['status' => session('status')]);
            });
        });

        app()->singleton(ResetPasswordViewResponse::class, function () {
            return new SimpleViewResponse(function($request) {
                return Inertia::render('Auth/ResetPassword', [
                    'email' => $request->input('email'),
                    'token' => $request->route('token'),
                ]);
            });
        });

        app()->singleton(RequestPasswordResetLinkViewResponse::class, function ($request) {
            return new SimpleViewResponse(function($request) {
                return Inertia::render('Auth/ForgotPassword', [
                    'status' => session('status'),
                ]);
            });
        });

        app()->singleton(RegisterViewResponse::class, function ($request) {
            return new SimpleViewResponse(function($request) {
                return Inertia::render('Auth/Register');
            });
        });
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Route::middleware('web')->group(function(){
            Route::middleware('guest')->group(function () {
                Route::get('register', [RegisteredUserController::class, 'create'])
                    ->name('register');

                Route::post('register', [RegisteredUserController::class, 'store']);

                Route::get('login', [AuthenticatedSessionController::class, 'create'])
                    ->name('login');

                Route::post('login', [AuthenticatedSessionController::class, 'store']);

                Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                    ->name('password.request');

                Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                    ->name('password.email');

                Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                    ->name('password.reset');

                Route::post('reset-password', [NewPasswordController::class, 'store'])
                    ->name('password.update');
            });

            Route::middleware('auth')->group(function () {
                Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
                    ->name('verification.notice');

                Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                    ->middleware(['signed', 'throttle:6,1'])
                    ->name('verification.verify');

                Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                    ->middleware('throttle:6,1')
                    ->name('verification.send');

                Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                    ->name('password.confirm');

                Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

                Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                    ->name('logout');
            });
        });
    }
}
