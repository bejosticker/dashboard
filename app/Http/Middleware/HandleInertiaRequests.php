<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default ke seluruh halaman.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = session('data');

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'name' => $user->name ?? null,
                    'username' => $user->username ?? null,
                    'level' => (int) ($user->level ?? 0),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'menu' => fn () => $this->menu(),
        ]);
    }

    /**
     * Menu navigasi sidebar (port dari resources/menu/verticalMenu.json).
     *
     * @return array<int, mixed>
     */
    private function menu(): array
    {
        $path = resource_path('menu/verticalMenu.json');

        if (! is_file($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true)['menu'] ?? [];
    }
}
