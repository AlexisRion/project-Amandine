<?php

namespace App\EventSubscriber;

use App\Entity\Domain;
use App\Repository\DomainRepository;
use App\Service\AccessTokenService;
use App\Service\AddYearService;
use App\Service\CreateDomainService;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AccessTokenService $accessTokenService,
        private CreateDomainService $createDomainService,
        private AddYearService $addYearService,
        private DomainRepository $domrepo,
        private RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['persistEntityAPI'],
            BeforeEntityUpdatedEvent::class => ['updateEntityAPI'],
        ];
    }

    /**
     * EventSubscriber that interrogates the Afnic API before
     * persisting Domain, and create the domain in the API.
     * @param BeforeEntityPersistedEvent $event
     * @return void
     * @throws \DateMalformedStringException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function persistEntityAPI(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Domain)) {
            return;
        }

        $accessToken = $this->accessTokenService->getAccessToken();
        $flash = $this->createDomainService->createDomain($entity->getName(), $accessToken);

        if ($flash['type'] === 'danger') {
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add($flash['type'], $flash['message']);
            return;
        }

        $domainArray = $flash['domainArray'];

        $creationDate = new \DateTimeImmutable($domainArray['creationDate']);
        $expirationDate = new \DateTimeImmutable($domainArray['expirationDate']);

        $event->getEntityInstance()->setCreatedAt($creationDate);
        $event->getEntityInstance()->setExpireAt($expirationDate);
        $event->getEntityInstance()->setIsToSuppress(false);
        $event->getEntityInstance()->setIsHistory(false);

        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add($flash['type'], $flash['message']);
    }

    /**
     *  EventSubscriber that interrogates the Afnic API before
     *  updating Domain about yearsToAdd, and add years to the domain in the API.
     * @param BeforeEntityPersistedEvent $event
     * @return void
     * @throws \DateMalformedIntervalStringException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function updateEntityAPI(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity->isToSuppress()) {
            return;
        }

        // Check if there is years to add
        if ($entity->getYearsToAdd() <= 0) {
            return;
        }

        $accessToken = $this->accessTokenService->getAccessToken();
        $flash = $this->addYearService->addYear($entity, $accessToken);

        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add($flash['type'], $flash['message']);
    }
}
