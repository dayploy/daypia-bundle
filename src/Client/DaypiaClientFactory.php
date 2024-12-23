<?php

declare(strict_types=1);

namespace Dayploy\DaypiaBundle\Client;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class DaypiaClientFactory
{
    public function __construct(
        private LoggerInterface $logger,
        private HttpClientInterface $client,
    ) {
    }

    public function createClient(
        ?HttpClientInterface $httpClient = null,
    ) {
        return new DaypiaClient(
            logger: $this->logger,
            client: $this->getHttpClient($httpClient),
        );
    }

    private function getHttpClient(
        ?HttpClientInterface $httpClient = null,
    ): HttpClientInterface {
        if (null !== $httpClient) {
            return $httpClient;
        }

        $daypiaBaseUrl = getenv('DAYPIA_BASE_URL') ? getenv('DAYPIA_BASE_URL'): 'https://api.daypia.com';
        $daypiaApiKey = getenv('DAYPIA_API_KEY');

        $options = [
            'base_uri' => $daypiaBaseUrl,
            'headers' => [
                'Authorization: \'Bearer '.$daypiaApiKey,
            ],
        ];

        return $this->client->withOptions($options);
    }
}
