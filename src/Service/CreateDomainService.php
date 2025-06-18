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
        private CheckDomainAvailabilityService $checkDomainAvailabilityService,
        private GetAuthorisationCodeService $getAuthorisationCodeService,
        private PersistDomainToDBService $persistDomainToDBService,
    )
    {
    }

    /**
     * Function that create a Domain object in the Afnic Api and persist it in the database.
     * Returns an associative array with 'type' and 'message' to put in a flash message.
     * @param Domain $domain
     * @param string $accessToken
     * @return string[]
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createDomain(string $domainName, string $accessToken): array
    {
        if (!$this->checkDomainAvailabilityService->checkDomain($domainName, $accessToken)) {
            return [
                'type' => 'danger',
                'message' => 'Nom de domaine non disponible',
            ];
        }

        //$this->getAuthorisationCodeService->getAuthorisationCode($domainName, $accessToken);

        //TODO make the request work
        $response = $this->httpClient->request(
            'POST',
            //'https://api.nic.fr/v1/domains', // API prod
            'https://api-sandbox.nic.fr/v1/domains', // API sandbox
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json' => [
//
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
                    'name' => $domainName,
                    'authorizationInformation' => 'Very1234secure',
                    "registrantClientId" => "CTC472084",
                ]
            ]
        );

        //TODO persist the newly created domain to DB
//        $this->persistDomainToDBService()

        return [
            'type' => 'success',
            'message' => 'Domaine créé avec succès',
        ];
    }
}
