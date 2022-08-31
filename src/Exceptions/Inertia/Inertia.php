<?php
namespace Lcg\Exceptions\Inertia;

class Inertia extends \Inertia\Inertia{
    protected static function getFacadeAccessor(): string
    {
        return InertiaResponseFactory::class;
    }
}
