<?php

namespace App\Controller;

use App\Repository\DomainRepository;
use App\Service\AccessTokenService;
use App\Service\DeleteDomainService;
use App\Service\GetDomainsService;
use App\Service\PersistDomainToDBService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DatabaseController extends AbstractController
{
    public function __construct(
        private DomainRepository $domRepo,
        private AccessTokenService $accessTokenService,
        private GetDomainsService $getDomainsService,
        private RequestStack $requestStack,
        private DeleteDomainService $deleteDomainService,
        private PersistDomainToDBService $persistDomainToDBService,
    ) {
    }

    #[Route('/admin/database/import', name: 'app_database_import', methods: ['GET'])]
    public function import(): Response
    {
        $accesstoken = $this->accessTokenService->getAccessToken();

        $domains = $this->getDomainsService->getDomains($accesstoken);
        $domainsDB = $this->domRepo->findBy(['isHistory' => false]);
        $count = 0;

        if (count($domainsDB) === $domains["totalElements"]) {
            $this->addFlash('success', 'Votre BDD est déjà à jour');
            return $this->redirect('/admin');
        }

        foreach ($domains["content"] as $domain) {
            $domainDB = $this->domRepo->findOneBy(['name' => $domain["name"]]);
            if (is_null($domainDB)) {
                $this->persistDomainToDBService->persistDomainToDB($domain);
                $count++;
            }
        }

        if ($count === 1) {
            $this->addFlash('success', '<strong>'. $count .'</strong> domaine importé dans la BDD');
        } else {
            $this->addFlash('success', '<strong>'. $count .'</strong> domaines importés dans la BDD');
        }

        return $this->redirect('/admin');
    }

    #[Route('/admin/database/delete/api', name: 'app_database_delete_api', methods: ['GET'])]
    public function delete(): Response
    {
        $accesstoken = $this->accessTokenService->getAccessToken();
        $count = 0;

        $domains = $this->getDomainsService->getDomains($accesstoken);

        foreach ($domains["content"] as $domain) {
            $flash = $this->deleteDomainService->deleteDomain($domain["name"], $accesstoken);
            if ($flash["type"] === "success") {
                $count++;
            }
        }

        $this->addFlash('success', '<strong>' . $count . '</strong> domaines supprimés');

        return $this->redirect('/admin');
    }
}
