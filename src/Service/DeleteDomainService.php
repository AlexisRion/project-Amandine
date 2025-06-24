<?php

namespace App\Service;

use App\Entity\Domain;
use App\Repository\DomainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeleteDomainService
{
    public function __construct(
        private EntityManagerInterface $em,
        private DomainRepository $domRepo,
        private HttpClientInterface $httpClient,
    )
    {
    }

    /**
     * Function that delete a domain from the Afnic API and change the domain to history in database.
     * @param Domain $domain
     * @param string $accessToken
     * @return void
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function deleteDomain(string $domainName, string $accessToken): array
    {
        $response = $this->httpClient->request(
            'DELETE',
            //'https://api.nic.fr/v1/domains/' . $domainName, // API prod
            'https://api-sandbox.nic.fr/v1/domains/' . $domainName, // API sandbox
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]
        );

        $responseCode = $response->getStatusCode();

        // Check if request to API is OK
        if ($responseCode !== 200) {
            return [
                'type' => 'danger',
                'message' => '<strong>Erreur ' . $responseCode . ' </strong>
                    Le domain <strong>' . $domainName . '</strong> n\'a pas pu être supprimé.',
                'logger' => 'Erreur ' . $responseCode . ': Le domain ' . $domainName . ' n\'a pas pu être supprimé.'
            ];
        }

        // Set Domain status to isHistory in DB
        $domain = $this->domRepo->findOneBy(['name' => $domainName]);
        $domain->setIsHistory(true);

        $this->em->persist($domain);
        $this->em->flush();

        return [
            'type' => 'success',
            'message' => 'Domaine <strong>' . $domainName . '</strong> supprimé avec succès',
            'logger' => 'Domaine ' . $domainName . ' supprimé avec succès',
        ];
    }
}
