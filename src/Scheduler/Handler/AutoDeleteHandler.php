<?php

namespace App\Scheduler\Handler;

use App\Repository\DomainRepository;
use App\Scheduler\Message\AutoDelete;
use App\Service\AccessTokenService;
use App\Service\DeleteDomainService;
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

    public function __invoke(AutoDelete $message): void
    {
        $this->logger->info('Début AutoDelete');

        // $domains in ArrayCollection format to access isEmpty() method
        $domains = new ArrayCollection($this->domRepo->getIsToSuppressToday());

        // If there is no Domain to suppress
        if ($domains->isEmpty()) {
            $this->logger->info('No domain to suppress');
            $this->logger->info('Fin AutoDelete');
            return;
        }

        $accessToken = $this->accessTokenService->getAccessToken();
        // Suppress Domain then log a message to the console
        foreach ($domains as $domain) {
            $flash = $this->deleteDomainService->deleteDomain($domain->getName(), $accessToken);
            $this->logger->info($flash['type'] . ': ' . $flash['logger']);
        }

        $this->logger->info('Fin AutoDelete');
    }
}
