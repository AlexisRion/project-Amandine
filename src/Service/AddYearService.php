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

    /**
     * Function that adds given number of years to a given domain.
     * Returns an associative array with 'type' and 'message' to put in a flash message.
     * @param Domain $domain
     * @param int $years
     * @param string $accessToken
     * @return string[]
     * @throws \DateMalformedIntervalStringException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
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

        $responseCode = $response->getStatusCode();

        // Check if request to API is OK
        if ($responseCode !== 200) {
            if ($responseCode === 400) {
                return [
                    'type' => 'danger',
                    'message' => '<strong>Erreur ' . $responseCode . ':</strong> impossible request. Le temps n\'a pas pu être ajouté',
                ];
            }

            return [
                'type' => 'danger',
                'message' => '<strong>Erreur ' . $responseCode . ':</strong> an internal error occurred. Le temps n\'a pas pu être ajouté',
            ];
        }

        $domain->setExpireAt($domain->getExpireAt()->add(new \DateInterval('P' . $years . 'Y')));
        $this->entityManager->persist($domain);
        $this->entityManager->flush();

        return [
            'type' => 'success',
            'message' => 'Ajout de ' . $years . ' années pour ' . $domain->getName(),
        ];
    }
}
