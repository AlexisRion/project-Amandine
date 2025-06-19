<?php

namespace App\EventSubscriber;

use App\Entity\Domain;
use App\Service\AccessTokenService;
use App\Service\CreateDomainService;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function PHPUnit\Framework\throwException;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AccessTokenService $accessTokenService,
        private CreateDomainService $createDomainService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['createEntityAPI'],
        ];
    }

    public function createEntityAPI(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Domain)) {
            return;
        }

        $accessToken = $this->accessTokenService->getAccessToken();
        $flash = $this->createDomainService->createDomain($entity->getName(), $accessToken);

        if ($flash['type'] === 'danger') {
            //TODO throw exception this name is already taken
            return;
        }

        $domainArray = $flash['domainArray'];

        $creationDate = new \DateTimeImmutable($domainArray['creationDate']);
        $expirationDate = new \DateTimeImmutable($domainArray['expirationDate']);

        $event->getEntityInstance()->setCreatedAt($creationDate);
        $event->getEntityInstance()->setExpireAt($expirationDate);
        $event->getEntityInstance()->setIsToSuppress(false);
        $event->getEntityInstance()->setIsHistory(false);
    }
}
