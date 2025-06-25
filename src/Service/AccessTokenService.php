<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccessTokenService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function getAccessToken(): string
    {
//        $username = $this->parameterBag->get('API_AFNIC_USERNAME'); // Username prod
        $username = 'd5650-user2'; // Username sandbox
        $password = $this->parameterBag->get('API_AFNIC_PASSWORD');

        $response = $this->httpClient->request(
            'POST',
//            'https://login.nic.fr/auth/realms/fr/protocol/openid-connect/token', // API prod
            'https://login-sandbox.nic.fr/auth/realms/fr/protocol/openid-connect/token', // API sandbox
            [
                'body' => [
                    'client_id' => 'registrars-api-client',
                    'username' => $username,
                    'password' => $password,
                    'grant_type' => 'password',
                ],
            ]
        );

        $responseCode = $response->getStatusCode();

        // Check if request to API is OK
        if ($responseCode !== 200) {
            return '';
        }

        $accessToken = $response->toArray();

        return $accessToken['access_token'];
    }
}
