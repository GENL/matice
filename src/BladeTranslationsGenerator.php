<?php


namespace Genl\Matice;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Translation\Translator;

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

            return <<<EOT
<!-- Matice Laravel Translations generated -->
<!-- Used cached translations at: $path -->
<script id="matice-translations">
  $generated
</script>
EOT;

        }

        $translations = json_encode($this->translations($locale));
        $appLocale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        $object = <<<EOT
const Matice = {
  locale: "$appLocale",
  fallbackLocale: "$fallbackLocale",
  translations: $translations
}
EOT;

        if ($wrapInHtml) {
            /** @noinspection BadExpressionStatementJS */
            return <<<EOT
<!-- Matice Laravel Translations generated -->
<script id="matice-translations">
  $object
</script>
EOT;
        } else {
            return $object;
        }
    }

    /**
     * Load all the translations array.
     *
     * @param string|null $locale
     * @return array
     */
    public function translations(?string $locale = null): array
    {
        $translations = Translator::list();

        if (isset($translations[$locale])) {
            // Loads translations of the locale
            $translations = $translations[$locale];
        }

        return $translations;
    }
}
