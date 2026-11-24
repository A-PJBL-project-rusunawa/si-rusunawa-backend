<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email', '');

            $email = is_string($email)
                ? Str::lower($email)
                : '';

            return Limit::perMinute(5)
                ->by($email . '|' . $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Terlalu banyak percobaan login. Silakan coba lagi beberapa saat.',
                    ], 429, $headers);
                });
        });
    }
}