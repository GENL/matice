<?php


namespace Genl\Matice;


use Genl\Matice\Exceptions\LocaleTranslationsFileOrDirNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BladeTranslationsGenerator
{
    private $maticeExportStatement = 'export {Matice}';

    /**
     * Loads the translations array and generates the html code containing the JavaScript
     * translation that will be used on the frontend.
     *
     * Note that when $useCache is set to true, $wrapInHtml also has to be true.
     *
     * @param string|string[]|null $locales
     *      the locales language to load. It can be a comma separated list of locales or an array.
     *      All translation are loaded if locale is null or empty. Default to null
     * @param bool $wrapInHtml
     * @param bool $useCache - Whether to use the cached generated script or to generate a new one.
     * @return string
     */
    public function generate($locales = null, bool $wrapInHtml = true, bool $useCache = false): string
    {
        $locales = is_array($locales ?? []) ?
            $locales :
            preg_split(
                '/,/',
                preg_replace('/\s/', '', $locales),
                null,
                PREG_SPLIT_NO_EMPTY
            );
        // Given a list of locales are set, we want to make sure the fallback local
        // is always there
        $locales = $locales ?
            collect(
                array_merge($locales, [config('app.fallback_locale')])
            )->unique()->toArray() :
            $locales;
        // when $useCache is set to true, $wrapInHtml also has to be true.
        // TODO: make it possible to return the matice object without wrapping the it
        //  in the HTML script when $useCache === true.
        abort_if(
            $useCache && !$wrapInHtml,
            400,
            'Cannot generate translations because when $useCache is true, $wrapInHtml also has to be true.'
        );
        $path = config('matice.generate_translations_path');
        // Use the cache if the translation file exists
        if ($useCache) {
            if (File::exists($path)) {
                $generatedTranslationFileContent = File::get($path);
                return $this->makeMaticeHtml(
                    $generatedTranslationFileContent,
                    true,
                    "Matice Laravel Translations generated", "Using cached translations"
                );
            }
            Log::warning("Trying to use the cached matice translations file but the file was not found.");
            error_log("Trying to use the cached matice translations file but the file was not found.");
        }
        if ($wrapInHtml) {
            return $this->makeMaticeHtml(
                $this->makeMaticeJSObject($locales),
                true,
                "Matice Laravel Translations generated"
            );
        } else {
            return $this->makeMaticeJSObject($locales);
        }
    }

    /**
     * @param string[] $locales
     * @return string
     * @throws LocaleTranslationsFileOrDirNotFoundException
     */
    private function makeMaticeJSObject(array $locales): string
    {
        $translations = json_encode($this->translations($locales));
        $appLocale = $locale ?? app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        return <<<EOT
const Matice = {
  locale: '$appLocale',
  fallbackLocale: '$fallbackLocale',
  translations: $translations
}

$this->maticeExportStatement
EOT;
    }


    /**
     * @param string $maticeJSObject
     * @param bool $shouldRemoveExportStatement
     * @param string ...$comments
     * @return string
     */
    private function makeMaticeHtml(
        string $maticeJSObject,
        bool $shouldRemoveExportStatement,
        ...$comments
    ): string
    {
        $maticeJSObject = $shouldRemoveExportStatement ?
            preg_replace("/$this->maticeExportStatement/",'', $maticeJSObject) :
            $maticeJSObject;
        $c = '';
        foreach ($comments as $comment) {
            $c .= "<!-- $comment -->\n";
        }
        return <<<EOT
<!-- Matice Laravel Translations generated -->
<script id="matice-translations">
    $c; $maticeJSObject;
</script>
EOT;
    }


    /**
     * Load all the translations array.
     *
     * @param string[] $locales
     * @return array
     * @throws LocaleTranslationsFileOrDirNotFoundException
     */
    public function translations(array $locales = []): array
    {
        $translations = MaticeServiceProvider::makeFolderFilesTree(config('matice.lang_directory'));
        Helpers::applyTranslationRestrictions($translations);
        $selectedTranslations = [];
        foreach($locales as $l) {
            if (isset($translations[$l])) {
                // Loads translations of the locale
                $selectedTranslations[$l] = $translations[$l];
            } else {
                throw new LocaleTranslationsFileOrDirNotFoundException($l);
            }
        }
        return $selectedTranslations ?: $translations;
    }
}
