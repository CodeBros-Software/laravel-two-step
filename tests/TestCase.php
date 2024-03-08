<?php

namespace CodeBros\TwoStep\Tests;

use CodeBros\TwoStep\Http\Middleware\TwoStepMiddleware;
use CodeBros\TwoStep\TwoStepServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [TwoStepServiceProvider::class];
    }

    /**
     * Load package alias.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            TwoStepMiddleware::class,
        ];
    }
}
