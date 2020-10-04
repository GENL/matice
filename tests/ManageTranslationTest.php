<?php

namespace Matice\Matice\Tests;

use Matice\Matice\Facades\Matice;
use Orchestra\Testbench\TestCase;
use Matice\Matice\MaticeServiceProvider;
use phpDocumentor\Reflection\Types\This;

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
    public function makeFolderFilesTree()
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
        $js = Matice::generate();

        $this->assertStringContainsString('<script type="text/javascript">', $js);
    }
}
