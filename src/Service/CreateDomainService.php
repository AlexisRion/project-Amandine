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

    /**
     * Function that create a Domain object in the Afnic Api and persist it in the database.
     * @param Domain $domain
     * @param string $accessToken
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createDomain(Domain $domain, string $accessToken): void
    {
        $response = $this->httpClient->request(
            'POST',
            //'https://api.nic.fr/v1/domains', // API prod
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

        //TODO persist the newly created domain to DB
    }
}
