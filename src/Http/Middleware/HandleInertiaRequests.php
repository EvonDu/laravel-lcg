<?php

namespace Lcg\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Lcg\Exceptions\Inertia\Inertia as InertiaExt;

class HandleInertiaRequests extends Middleware
{
    /**
     * Handle Request
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        InertiaExt::version(function () use ($request) {
            return $this->version($request);
        });

        InertiaExt::share($this->share($request));
        InertiaExt::setRootView($this->rootView($request));

        $response = $next($request);
        $response->headers->set('Vary', 'X-Inertia');

        if (! $request->header('X-Inertia')) {
            return $response;
        }

        if ($request->method() === 'GET' && $request->header('X-Inertia-Version', '') !== InertiaExt::getVersion()) {
            $response = $this->onVersionChange($request, $response);
        }

        if ($response->isOk() && empty($response->getContent())) {
            $response = $this->onEmptyResponse($request, $response);
        }

        if ($response->getStatusCode() === 302 && in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
            $response->setStatusCode(303);
        }

        return $response;
    }
}
