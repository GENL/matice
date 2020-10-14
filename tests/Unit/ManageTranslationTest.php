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

        config()->set('matice.lang_directory', $this->langDir);
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
        // dd(__DIR__ . ('/test/assets/lang'));
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

        $bladeOutPut1 = Blade::compileString('@translations');
        $bladeOutPut2 = Blade::compileString('@translations(\'en\')');

        $this->assertTrue("<?php echo app()->make('matice')->generate(); ?>" === $bladeOutPut1);
        $this->assertTrue("<?php echo app()->make('matice')->generate('en'); ?>" === $bladeOutPut2);

        $this->assertStringContainsString('<script type="text/javascript">', $jsOutput);
    }
}
