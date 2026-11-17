<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Tests;

use Juaniquillo\BackendComponents\BackendComponentsServiceProvider;
use Juaniquillo\TableComponentAction\TableComponentActionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            TableComponentActionServiceProvider::class,
            BackendComponentsServiceProvider::class,
        ];
    }
}
