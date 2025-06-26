<?php

namespace App\Controller;

use App\Service\AccessTokenService;
use App\Service\CheckDomainAvailabilityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CheckDomainController extends AbstractController
{
    public function __construct(
        private AccessTokenService $accessTokenService,
        private CheckDomainAvailabilityService $checkDomainAvailabilityService,
    )
    {
    }

    #[Route('admin/checkAvailable', name: 'app_check_available', methods: ['GET', 'POST'])]
    public function checkNameAvailability(Request $request): Response
    {
        $accessToken = $this->accessTokenService->getAccessToken();
        $domainName = $request->get('domainName');

        $available = $this->checkDomainAvailabilityService->checkDomain($domainName, $accessToken);

        if (!$available) {
            $this->addFlash('danger',  'Le nom de domaine <strong>' . $domainName . '</strong> n\'est pas disponible');
            return $this->redirect('/admin');
        }

        $this->addFlash('success',  'Le nom de domaine <strong>' . $domainName . '</strong> est disponible');
        return $this->redirect('/admin');
    }
}
