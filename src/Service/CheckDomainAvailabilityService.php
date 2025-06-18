<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CheckDomainAvailabilityService
{
    public function __construct(
        private HttpClientInterface $httpClient,
    )
    {
    }

    /**
     * Function that checks if a domain name is available.
     * @param string $domainName
     * @param string $accessToken
     * @return bool
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function checkDomain(string $domainName, string $accessToken): bool
    {
        $response = $this->httpClient->request(
            'POST',
            //'https://api.nic.fr/v1/domains/check', // API prod
            'https://api-sandbox.nic.fr/v1/domains/check', // API sandbox
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json' => [
                    'names' => [
                        $domainName,
                    ]
                ]
            ]
        );

        $result = $response->toArray();
        $content = $result["response"][0];

        return($content['available']);
    }
}
