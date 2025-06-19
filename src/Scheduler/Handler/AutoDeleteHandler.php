<?php

namespace App\Scheduler\Handler;

use App\Repository\DomainRepository;
use App\Scheduler\Message\AutoDelete;
use App\Service\AccessTokenService;
use App\Service\DeleteDomainService;
use App\Service\GetDomainsService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;
use function PHPUnit\Framework\isEmpty;

#[AsMessageHandler]
final class AutoDeleteHandler
{
    public function __construct(
        private AccessTokenService $accessTokenService,
        private DomainRepository $domRepo,
        private DeleteDomainService $deleteDomainService,
        private LoggerInterface $logger,
    ) {
    }
    public function __invoke(AutoDelete $message)
    {
        $domains = $this->domRepo->getIsToSuppress();
//        dd($domains);
        if (isEmpty($domains)) {
            $this->logger->error('No domains to suppress');
        }

        $accessToken = $this->accessTokenService->getAccessToken();
        foreach ($domains as $domain) {
            $flash = $this->deleteDomainService->deleteDomain($domain->getName(), $accessToken);
            $this->logger->error($flash['type'] . $domain->getName() . $flash['message']);
        }
    }
}
