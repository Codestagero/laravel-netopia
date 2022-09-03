<?php

namespace iRealWorlds\Netopia\Contracts;

use Illuminate\Contracts\Config\Repository as ConfigurationRepository;
use Illuminate\Support\Collection;

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

    /**
     * Build a netopia URL.
     *
     * @param string|string[] $segments
     * @param array<string, mixed> $parameters
     * @return string
     */
    public function buildUrl(string|array $segments, array $parameters = []): string
    {
        // Make sure the segments property is an array
        if (!\is_array($segments)) {
            $segments = [$segments];
        }

        // Add the base url to the segments array
        $segments = [$this->baseUrl, ...$segments];

        // Prepare the parameters
        $parameters = Collection::make($parameters)->map(fn (mixed $value, string $key) => $key . '=' . urlencode($value))->values()->toArray();

        // Build the final URL
        return implode('/', $segments) . '?' . implode('&', $parameters);
    }
}
