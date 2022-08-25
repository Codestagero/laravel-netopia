<?php

namespace iRealWorlds\Netopia\Tests;

use iRealWorlds\Netopia\Providers\NetopiaServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app): array
    {
        return [NetopiaServiceProvider::class];
    }
}
