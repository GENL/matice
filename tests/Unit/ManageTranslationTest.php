<?php

namespace Genl\Matice\Tests\Unit;

use Genl\Matice\Facades\Matice;
use Genl\Matice\Tests\TestCase;
use Illuminate\Support\Facades\Blade;
use Genl\Matice\MaticeServiceProvider;
use Illuminate\Support\Facades\Lang;

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

    public function test_load_translations()
    {
        $translations = Matice::translations();

        $this->assertIsArray($translations);

        $this->assertArrayHasKey('en', $translations);
        $this->assertCount(4, $translations['en']);

        $this->assertStringContainsString("Hi! I'm a json translation text.", json_encode($translations['en']));

        $this->assertArrayHasKey('From default json file', $translations['en']);
        $this->assertContains("Hi! I'm from a default json translation file.", $translations['en']);
    }

    public function test_load_translations_from_additional_json_paths()
    {
        $supportsJsonPaths = method_exists(Lang::getLoader(), 'jsonPaths') || method_exists(Lang::getLoader(), 'getJsonPaths');

        if (!$supportsJsonPaths) {
            $this->markTestSkipped('The current Laravel version does not support loading translations from additional json paths.');
        }

        Lang::addJsonPath(__DIR__ . ('/../../tests/assets/additional_json_files'));

        $translations = Matice::translations();

        $this->assertIsArray($translations);

        $this->assertArrayHasKey('en', $translations);
        $this->assertCount(5, $translations['en']);

        $this->assertStringContainsString("Hi! I'm a json translation text.", json_encode($translations['en']));

        $this->assertArrayHasKey('From default json file', $translations['en']);
        $this->assertContains("Hi! I'm from a default json translation file.", $translations['en']);

        $this->assertArrayHasKey('From additional json file', $translations['en']);
        $this->assertContains("Hi! I'm from an additional json translation file.", $translations['en']);
    }

    public function test_no_fail_when_translations_json_paths_doesnt_exist()
    {
        $supportsJsonPaths = method_exists(Lang::getLoader(), 'jsonPaths') || method_exists(Lang::getLoader(), 'getJsonPaths');
        if (!$supportsJsonPaths) {
            $this->markTestSkipped('The current Laravel version does not support loading translations from additional json paths.');
        }
        Lang::addJsonPath(__DIR__ . ('/../../tests/assets/non_existent_route'));
        $this->expectNotToPerformAssertions();
        Matice::translations();
    }

    public function test_generate_translation_js()
    {
        $jsOutput = Matice::generate();

        $this->assertStringContainsString('<script id="matice-translations">', $jsOutput);


        // ================== Test the blade directive ===================

        $bladeOutPut1 = Blade::compileString('@translations');
        $bladeOutPut2 = Blade::compileString('@translations(\'en\')');

        $this->app->env = 'production';

        $bladeOutPut3 = Blade::compileString('@translations()');

        $this->app->env = 'testing';

        $this->assertTrue("<?php echo app()->make('matice')->generateFromBlade(false, null); ?>" === $bladeOutPut1);
        $this->assertTrue("<?php echo app()->make('matice')->generateFromBlade(false, 'en'); ?>" === $bladeOutPut2);
        $this->assertTrue("<?php echo app()->make('matice')->generateFromBlade(true, null); ?>" === $bladeOutPut3);

        $bladeOutPut4 = Blade::compileString("@translations('en', ['only' => ['example1']])");
        $this->assertTrue("<?php echo app()->make('matice')->generateFromBlade(false, 'en', ['only' => ['example1']]); ?>" === $bladeOutPut4);
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

    public function test_directive_options_only_with_short_names()
    {
        $translations = Matice::translations(['en'], [
            'only' => ['example1', 'example2'],
        ]);

        $this->assertArrayHasKey('example1', $translations['en']);
        $this->assertArrayHasKey('example2', $translations['en']);
        $this->assertArrayNotHasKey('folder', $translations['en']);
    }

    public function test_directive_options_except_with_short_names()
    {
        $translations = Matice::translations(['en'], [
            'except' => ['example1', 'example2'],
        ]);

        $this->assertArrayNotHasKey('example1', $translations['en']);
        $this->assertArrayNotHasKey('example2', $translations['en']);
        $this->assertArrayHasKey('folder', $translations['en']);
    }

    public function test_directive_options_only_overrides_config()
    {
        config(['matice.only' => [
            'en/folder',
        ]]);

        $translations = Matice::translations(['en'], [
            'only' => ['example1'],
        ]);

        $this->assertArrayHasKey('example1', $translations['en']);
        $this->assertArrayNotHasKey('folder', $translations['en']);
    }

    public function test_directive_options_absent_key_falls_back_to_config()
    {
        config(['matice.except' => [
            'en/example1',
        ]]);

        $translations = Matice::translations(['en'], [
            'only' => ['example1', 'example2'],
        ]);

        $this->assertArrayNotHasKey('example1', $translations['en']);
        $this->assertArrayHasKey('example2', $translations['en']);
    }

    public function test_directive_options_support_full_paths()
    {
        $translations = Matice::translations(['en'], [
            'only' => ['en/example1'],
        ]);

        $this->assertArrayHasKey('example1', $translations['en']);
        $this->assertArrayNotHasKey('example2', $translations['en']);
    }

    public function test_generate_from_blade_disables_cache_when_options_present()
    {
        $path = 'fake/assets/js/matice_translations.js';
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, "const Matice = { cached: true };");

        try {
            $output = app('matice')->generateFromBlade(true, 'en', [
                'only' => ['example1'],
            ]);

            $this->assertStringNotContainsString('cached: true', $output);
            $this->assertStringContainsString('example1', $output);
        } finally {
            @unlink($path);
        }
    }
}
