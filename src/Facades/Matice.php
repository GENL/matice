<?php

namespace Genl\Matice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array translations(?string $locale = null) - Load all the translations array.
 *
 * @see \Genl\Matice\BladeTranslationsGenerator
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
