<?php

namespace App\Service;

use App\Entity\Domain;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CreateDomainService
{
    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $httpClient,
    )
    {
    }

    public function createDomain(Domain $domain, string $accessToken): void
    {
        $response = $this->httpClient->request(
            'POST',
            //'https://api.nic.fr/v1/domains/' . $domain->getName(), // API prod
            'https://api-sandbox.nic.fr/v1/domains', // API sandbox
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json' => [
                    'contacts' => [
                        [
                            'clientId' => 'CTC472084',
                            'role' => 'ADMINISTRATIVE'
                        ],
                        [
                            'clientId' => 'CTC472084',
                            'role' => 'TECHNICAL'
                        ]
                    ],
                    'durationInYears' => 1,
                    'name' => 'test1.fr',
                    'authorizationInformation' => '31F7KUCCJLqe-334',
                ]
            ]
        );

        $status = $response->getStatusCode();
        $content = $response->getContent();
        dd($status, $content, $response);
    }
}
