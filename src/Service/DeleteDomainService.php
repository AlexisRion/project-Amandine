<?php

namespace App\Service;

use App\Entity\Domain;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeleteDomainService
{
    public function __construct(
        //private EntityManagerInterface $em,
        private HttpClientInterface $httpClient,
    )
    {
    }

    public function deleteDomain(Domain $domain, string $accessToken): void
    {
        $response = $this->httpClient->request(
            'DELETE',
            //'https://api.nic.fr/v1/domains/' . $domain->getName(), // API prod
            'https://api-sandbox.nic.fr/v1/domains/' . $domain->getName(), // API sandbox
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]
        );

        $status = $response->getStatusCode();
        $content = $response->getContent();
        dd($status, $content, $response);
    }
}
