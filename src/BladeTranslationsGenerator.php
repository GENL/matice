<?php


namespace Matice\Matice;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator;

class BladeTranslationsGenerator
{
    /**
     * Load the translations array and the generate a the html code to paste to the page.
     *
     * @return string
     */
    public function generate() : string
    {
        $translations = json_encode($this->translations());

        return <<<EOT
<script type="text/javascript">
    const Matice = {
        'translations': $translations,
    }
</script>
EOT;
    }

    /**
     * Load all the translations array.
     *
     * @param string|null $path
     * @return array
     */
    public function translations(): array
    {
        return Translator::list();
    }
}
