<?php

namespace App\Service;

use App\Entity\Domain;
use Doctrine\ORM\EntityManagerInterface;


class PersistDomainToDBService
{
    public function __construct(
        private EntityManagerInterface $em,
    )
    {
    }

    public function persistDomainToDB(Array $domainArray): void
    {
        $domain = new Domain();
        $creationDate = new \DateTimeImmutable($domainArray['creationDate']);
        $expirationDate = new \DateTimeImmutable($domainArray['expirationDate']);

        $domain->setName($domainArray['name']);
        $domain->setCreatedAt($creationDate);
        $domain->setExpireAt($expirationDate);
        $domain->setIsToSuppress(false);
        $domain->setIsHistory(false);

        $this->em->persist($domain);
        $this->em->flush();
    }
}
