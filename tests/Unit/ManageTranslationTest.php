<?php

namespace Genl\Matice\Tests\Unit;

use Genl\Matice\Facades\Matice;
use Genl\Matice\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use Genl\Matice\MaticeServiceProvider;

class ManageTranslationTest extends TestCase
{

    protected $langDir = __DIR__ . ('/../../tests/assets/lang');

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'matice.lang_directory' => $this->langDir,
            'matice.use_generated_translations_file_in_prod' => true,
            'matice.generate_translations_path' => 'fake/assets/js/matice_translations.js'
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [MaticeServiceProvider::class];
    }

    /**
     * @test
     */
    public function loadTranslations()
    {
        $translations = Matice::translations($this->langDir);

        $this->assertIsArray($translations);

        $this->assertArrayHasKey('en', $translations);

        $this->assertCount(3, $translations['en']);

        $this->assertStringContainsString("Hi! I'm a json translation text.", json_encode($translations));
    }

    /**
     * @test
     */
    public function generateTranslationJs()
    {
        $jsOutput = Matice::generate();

        $this->assertStringContainsString('<script type="text/javascript">', $jsOutput);


        // ================== Test the blade directive ===================

        $bladeOutPut1 = Blade::compileString('@translations');
        $bladeOutPut2 = Blade::compileString('@translations(\'en\')');

        $this->app->env = 'production';

        $bladeOutPut3 = Blade::compileString('@translations()');

        $this->app->env = 'testing';

        $this->assertTrue("<?php echo app()->make('matice')->generate(null, true, false); ?>" === $bladeOutPut1);
        $this->assertTrue("<?php echo app()->make('matice')->generate('en', true, false); ?>" === $bladeOutPut2);
        $this->assertTrue("<?php echo app()->make('matice')->generate(null, true, true); ?>" === $bladeOutPut3);
    }
}
