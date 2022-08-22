<?php
namespace Lcg\Exceptions;

class Inertia extends \Inertia\Inertia{
    protected static function getFacadeAccessor(): string
    {
        return InertiaResponseFactory::class;
    }
}
