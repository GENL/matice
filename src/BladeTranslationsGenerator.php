<?php


namespace Genl\Matice;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator;

class BladeTranslationsGenerator
{
    /**
     * Load the translations array and the generate a the html code to paste to the page.
     *
     * @param string|null $locale
     *      the locale language to load. All translation are loaded if locale is null. Default to null
     * @return string
     */
    public function generate(?string $locale = null) : string
    {
        $translations = json_encode($this->translations());
        $locale = app()->getLocale();
        $fallbackLocale = config('config.app.fallback_locale');

        /** @noinspection BadExpressionStatementJS */
        return <<<EOT
<script type="text/javascript">
// noinspection JSAnnotator
const Matice = {
    translations: $translations,
    locale: $locale,
    fallbackLocale: $fallbackLocale
}
</script>
EOT;
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
            $translations = $translations[$locale];
        }

        return $translations;
    }
}
