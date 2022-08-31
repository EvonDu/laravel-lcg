<?php
namespace Lcg\Exceptions\Inertia;

use Illuminate\Contracts\Support\Arrayable;
use Inertia\Response;

class InertiaResponseFactory extends \Inertia\ResponseFactory{
    /**
     * @param  string  $component
     * @param  array|Arrayable  $props
     * @return Response
     */
    public function render(string $component, $props = []): Response
    {
        if ($props instanceof Arrayable) {
            $props = $props->toArray();
        }

        return new InertiaResponse(
            $component,
            array_merge($this->sharedProps, $props),
            $this->rootView,
            $this->getVersion()
        );
    }
}
