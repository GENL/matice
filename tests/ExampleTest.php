<?php

namespace Matice\Matice\Tests;

use Orchestra\Testbench\TestCase;
use Matice\Matice\MaticeServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [MaticeServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
