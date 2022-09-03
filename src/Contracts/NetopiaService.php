<?php

namespace Codestage\Netopia\Contracts;

use Illuminate\Contracts\Config\Repository as ConfigurationRepository;

abstract class NetopiaService
{
    /**
     * The base URL used for requests to Netopia.
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * The path to the public certificate path.
     *
     * @var mixed
     */
    protected mixed $certificatePath;

    /**
     * NetopiaService constructor method.
     */
    public function __construct(ConfigurationRepository $configuration)
    {
        $this->baseUrl = match ($configuration->get('netopia.environment')) {
            'sandbox' => 'http://sandboxsecure.mobilpay.ro',
            default => 'https://secure.mobilpay.ro',
        };
        $this->certificatePath = $configuration->get('netopia.certificate_path.public');
    }
}
