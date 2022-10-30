<?php

namespace Codestage\Netopia\Tests;

use Codestage\Netopia\Providers\NetopiaServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\WithFaker;
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

        $configuration = $this->app->make(Repository::class);
        $configuration->set('netopia.certificate_path.public', base_path('certificates/' . env('NETOPIA_PUBLIC_FILE', 'netopia.cer')));
        $configuration->set('netopia.certificate_path.secret', base_path('certificates/' . env('NETOPIA_SECRET_FILE', 'netopia.key')));

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
