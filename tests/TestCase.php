<?php

namespace iRealWorlds\Netopia\Tests;

use Illuminate\Support\Facades\Config;
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
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->setBasePath(__DIR__ . '/../');
        Config::set('netopia.certificate_path.public', base_path('certificates/' . env('NETOPIA_PUBLIC_FILE', 'netopia.cer')));
        Config::set('netopia.certificate_path.secret', base_path('certificates/' . env('NETOPIA_SECRET_FILE', 'netopia.key')));
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app): array
    {
        return [NetopiaServiceProvider::class];
    }
}
