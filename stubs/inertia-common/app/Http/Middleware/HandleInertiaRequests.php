<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'lte' => [
                "app" => [
                    "logo" => null,
                    "name" => config('app.name'),
                    "version" => "1.0.0",
                ],
                "header" => [
                    "links" => [
                        ["title" => "Home", "url" => url('/')],
                    ],
                    "messages" => [
                        //[ "url" => "#", "img" => null, "title" => "Example", "text" => "example", "time" => "Now" ],
                    ],
                    "notifications" => [
                        //[ "url" => "#", "icon" => "fas fa-users", "text" => "Example", "time" => "Now"],
                    ],
                ],
                "footer" => [
                    "text" => "Copyright ©2022 Laravel."
                ],
                "navs" => include(base_path("routes/navigations.php")),
            ]
        ]);
    }
}
