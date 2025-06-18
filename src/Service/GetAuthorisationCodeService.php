<?php

namespace App\Service;

use App\Entity\Domain;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetAuthorisationCodeService
{
    public function __construct(
        private HttpClientInterface $httpClient,
    )
    {
    }

    public function getAuthorisationCode(string $domainName, string $accessToken): string
    {
        $response = $this->httpClient->request(
            'POST',
            //'https://api.nic.fr/v1/domains/' . $domain->getName(), // API prod
            'https://api-sandbox.nic.fr/v1/registrar/authorization-code-requests', // API sandbox
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json' => [
                    'domainName' => $domainName,
                    'registrantClientId' => 'CTC472084',
                    'justification' => "Parce que c'est mon PROOOOJET !",
                ]
            ]
        );

        $status = $response->getStatusCode();
        $content = $response->getContent();
        dd($status, $content, $response);
    }
}
