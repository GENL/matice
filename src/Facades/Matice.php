<?php

namespace Matice\Matice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Matice\Matice\Skeleton\SkeletonClass
 */
class Matice extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'matice';
    }
}
