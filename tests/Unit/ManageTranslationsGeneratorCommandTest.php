<?php

namespace Genl\Matice\Tests\Unit;

use Genl\Matice\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ManageTranslationsGeneratorCommandTest // extends TestCase
{
    protected function tearDown(): void
    {
        if (file_exists(base_path('resources/assets/js')) && is_dir(base_path('resources/assets/js'))) {
            array_map(function ($file) {
                unlink($file);
            }, glob(base_path('resources/assets/js/*')));
        }

        parent::tearDown();
    }
}
