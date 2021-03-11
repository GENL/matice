<?php


namespace Genl\Matice\Exceptions;

/**
 * Throws when a user tries to generate translations for a locale that does not exist(The translation folder does not exist or the json file does not exist).
 *
 * Class LocaleTranslationsFileOrDirNotFoundException
 * @package Genl\Matice\Exceptions
 */
class LocaleTranslationsFileOrDirNotFoundException extends \Exception
{
    public function __construct(string $locale)
    {
        parent::__construct("Translations not found for locale: `$locale`");
    }
}
