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
        $translations = Matice::translations();

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

        $this->assertStringContainsString('<script id="matice-translations">', $jsOutput);


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

    public function test_namespaces_can_be_excepted()
    {
        config(['matice.except' => [
            'en/example1/', // Works with ile without extension
            'en/example2.json', // Works with file with extension
        ]]);

        $translations = Matice::translations();

        $this->assertArrayNotHasKey('example1', $translations['en']);
        $this->assertArrayNotHasKey('example2', $translations['en']);
        $this->assertArrayHasKey('folder', $translations['en']);

        config(['matice.except' => [
            'en/folder', // Works with folder
        ]]);

        $translations = Matice::translations();

        $this->assertArrayNotHasKey('folder', $translations['en']);
    }

    public function test_only_certain_namespaces_can_be_exported()
    {
        config(['matice.only' => [
            'en/example1/',
            'en/example2/',
        ]]);

        $translations = Matice::translations();

        $this->assertArrayHasKey('example1', $translations['en']);
        $this->assertArrayHasKey('example2', $translations['en']);
        $this->assertArrayNotHasKey('folder', $translations['en']);
    }

    /**
     * When a namespace is included and excepted at the same time, it considered excepted.
     */
    public function test_only_namespaces_can_be_both_exported_and_excepted()
    {
        config(['matice.only' => [
            'en/example1/',
            'en/example2/',
        ]]);

        config(['matice.except' => [
            'en/example2/',
        ]]);

        $translations = Matice::translations();

        $this->assertArrayHasKey('example1', $translations['en']);
        $this->assertArrayNotHasKey('example2', $translations['en']);
        $this->assertArrayNotHasKey('folder', $translations['en']);
    }
}
