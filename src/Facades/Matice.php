<?php

namespace Genl\Matice\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array translations(array $locales = [], array $options = []) - Load all the translations array.
 * @method static string generate(string|string[]|null $locales = null, bool $wrapInHtml = true, bool $useCache = false, bool $hasExport = true, array $options = []) - Load the translations array and generate the html code to paste to the page.
 * @method static string generateFromBlade(bool $useCache, string|string[]|null $locales = null, array $options = []) - Blade entry-point for @translations.
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
