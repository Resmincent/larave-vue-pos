<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'auth' => function () {
                $user = Auth::user();

                if ($user) {
                    return [
                        'user' => [
                            'id'    => $user->id,
                            'name'  => $user->name,
                            'email' => $user->email,
                            'roles'  => $user->getRoleNames()->first(), // langsung satu string
                        ],
                    ];
                }

                return ['user' => null];
            },
        ]);
    }
}
