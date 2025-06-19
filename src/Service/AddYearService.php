<?php

namespace App\Service;

use App\Entity\Domain;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AddYearService
{
    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $httpClient,
        private GetDomainsService $getDomainsService,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function addYear(Domain $domain, int $years, string $accessToken): array
    {
        // date_format to get rid of the h:m:s of the dateTime
        $expireAt = date_format($domain->getExpireAt(), 'Y-m-d');

        $response = $this->httpClient->request(
            'POST',
            //'https://api.nic.fr/v1/domains/renew', // API prod
            'https://api-sandbox.nic.fr/v1/domains/renew', // API sandbox
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json' => [
                    'name' => $domain->getName(),
                    'currentExpirationDate' => $expireAt,
                    'durationInYears' => $years,
                ]
            ]
        );

        $domain->setExpireAt($domain->getExpireAt()->add(new \DateInterval('P' . $years . 'Y')));
        $this->entityManager->persist($domain);
        $this->entityManager->flush();

        return [
            'type' => 'success',
            'message' => 'Ajout de ' . $years . ' années pour ' . $domain->getName(),
        ];
    }
}
