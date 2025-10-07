<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Ambil quote inspirasi Laravel
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),

            // Nama aplikasi
            'name' => config('app.name'),

            // Kutipan acak
            'quote' => [
                'message' => trim($message),
                'author'  => trim($author),
            ],

            // Data auth.user yang aman (hanya field penting)
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id'    => $request->user()->id,
                        'name'  => $request->user()->name,
                        'email' => $request->user()->email,
                        'role'  => $request->user()->getRoleNames()->first(),
                        // atau semua roles:
                        // 'roles' => $request->user()->getRoleNames()->toArray(),
                    ]
                    : null,
            ],

            // Status sidebar (true/false) dari cookie
            'sidebarOpen' => ! $request->hasCookie('sidebar_state')
                || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
