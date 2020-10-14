<?php

namespace Genl\Matice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array translations(?string $locale = null) - Load all the translations array.
 * @method static array generate(?string $locale = null, bool $wrapInHtml = true, bool $useCache = false) - Load the translations array and the generate a the html code to paste to the page.
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
