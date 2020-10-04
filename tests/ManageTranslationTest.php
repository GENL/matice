<?php

namespace Matice\Matice\Tests;

use Illuminate\Support\Facades\Blade;
use Matice\Matice\Facades\Matice;
use Orchestra\Testbench\TestCase;
use Matice\Matice\MaticeServiceProvider;

class ManageTranslationTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('config.lang_directory', './assets/lang');
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
        // $translations = app()->make('matice')->translations('./assets/lang');
        $translations = Matice::translations('./assets/lang');

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

        $bladeOutPut1 = Blade::compileString('@translations');
        $bladeOutPut2 = Blade::compileString('@translations(\'en\')');

        $this->assertTrue("<?php echo app()->make('matice')->generate(); ?>" === $bladeOutPut1);
        $this->assertTrue("<?php echo app()->make('matice')->generate('en'); ?>" === $bladeOutPut2);

        $this->assertStringContainsString('<script type="text/javascript">', $jsOutput);
    }
}
