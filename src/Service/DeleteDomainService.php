<?php

namespace App\Service;

use App\Entity\Domain;
use App\Repository\DomainRepository;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
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

        // Set Domain status to isHistory in DB
        $domain = $this->domRepo->findOneBy(['name' => $domainName]);
        $domain->setIsHistory(true);

        $this->em->persist($domain);
        $this->em->flush();

        return [
            'type' => 'success',
            'message' => 'Domaine ' . $domainName . ' supprimé avec succès',
        ];
    }
}
