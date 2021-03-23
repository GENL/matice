<?php


namespace Genl\Matice;


use Genl\Matice\Exceptions\LocaleTranslationsFileOrDirNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class BladeTranslationsGenerator
{
    /**
     * Load the translations array and the generate a the html code to paste to the page.
     *
     * @param string|null $locale
     *      the locale language to load. All translation are loaded if locale is null. Default to null
     * @param bool $wrapInHtml
     * @param bool $useCache - Whether to use the cached generated script or to generate a new one.
     * @return string
     */
    public function generate(?string $locale = null, bool $wrapInHtml = true, bool $useCache = false): string
    {
        $path = config('matice.generate_translations_path');
        if ($useCache) {
            // Generate the file if it does not exits
            if (! File::exists($path)) {
                Artisan::call('matice:generate');
            }

            $generated = File::get($path);

            return $this->makeMaticeHtml($generated, 'Matice Laravel Translations generated', "Use cached translations");
        }

        if ($wrapInHtml) {
            return $this->makeMaticeHtml($this->makeMaticeObject($locale), 'Matice Laravel Translations generated');
        } else {
            return $this->makeMaticeObject($locale);
        }
    }

    private function makeMaticeObject(?string $locale): string
    {
        $translations = json_encode($this->translations($locale));
        $appLocale = $locale ?? app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        return <<<EOT
const Matice = {
  locale: '$appLocale',
  fallbackLocale: '$fallbackLocale',
  translations: $translations
}
EOT;
    }

    /**
     * @param string $maticeObject
     * @param string ...$comments
     * @return string
     */
    private function makeMaticeHtml(string $maticeObject, ...$comments)
    {
        $c = '';

        foreach ($comments as $comment) {
            $c .= "<!-- $comment -->\n";
        }

        return <<<EOT
<!-- Matice Laravel Translations generated -->
<script id="matice-translations">
    $c; $maticeObject;
</script>
EOT;
    }


    /**
     * Load all the translations array.
     *
     * @param string|null $locale
     * @return array
     * @throws LocaleTranslationsFileOrDirNotFoundException
     */
    public function translations(?string $locale = null): array
    {
        $translations = MaticeServiceProvider::makeFolderFilesTree(config('matice.lang_directory'));
        Helpers::applyTranslationRestrictions($translations);

        if (! is_null($locale)) {
            if (isset($translations[$locale])) {
                // Loads translations of the locale
                $translations = [$locale => $translations[$locale]];
            } else {
                throw new LocaleTranslationsFileOrDirNotFoundException($locale);
            }
        }

        return $translations;
    }
}
