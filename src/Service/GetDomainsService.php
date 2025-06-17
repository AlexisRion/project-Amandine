<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetDomainsService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function getDomains(string $accessToken): array
    {
        $page = 0; // N° de page
        $pageSize = 50; // Nombre de résultats retournés par page (100 max)

        $response = $this->httpClient->request(
            'GET',
            //'https://api.nic.fr/v1/domains', // API prod
            'https://api-sandbox.nic.fr/v1/domains', // API sandbox
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'query' => [
                    'page' => $page,
                    'pageSize' => $pageSize
                ]
            ]
        );

        $domains = $response->toArray();
/*
        while (count($domains) < $domains['totalElements']) {
            $page++;

            $response = $this->httpClient->request(
                'GET',
                //'https://api.nic.fr/v1/domains', // Api prod
                'https://api-sandbox.nic.fr/v1/domains',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ],
                    'query' => [
                        'page' => $page,
                        'pageSize' => $pageSize
                    ]
                ]
            );

            $domainsPage = $response->toArray();

            for ($i = 0; $i < $pageSize; $i++) {
                $test[$i + $pageSize * $page] = $domainsPage['content'][$i];
                array_push($domains['content'],$test[$i + $pageSize]);
                if ($i + $pageSize * $page === $domains['totalElements'] - 1) {
                    return $domains;
                }
            }
        }
*/
        return $domains;
    }
}
