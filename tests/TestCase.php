<?php

namespace Codestage\Netopia\Tests;

use Codestage\Netopia\Providers\NetopiaServiceProvider;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Mockery;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use WithFaker;

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

        $this->setUpFaker();
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app): array
    {
        return [NetopiaServiceProvider::class];
    }
}
