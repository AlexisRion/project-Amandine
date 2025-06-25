<?php

namespace App\Service;

use App\Entity\Domain;
use App\Repository\DomainRepository;
use Doctrine\ORM\EntityManagerInterface;
use function PHPUnit\Framework\isNull;


class PersistDomainToDBService
{
    public function __construct(
        private EntityManagerInterface $em,
        private DomainRepository $domRepo,
    )
    {
    }

    /**
     * Function that persist a Domain object (in array format) from the Afnic API to a Domain object in the database.
     * @param array $domainArray
     * @return void
     * @throws \DateMalformedStringException
     */
    public function persistDomainToDB(Array $domainArray): void
    {
        $creationDate = new \DateTimeImmutable($domainArray['creationDate']);
        $expirationDate = new \DateTimeImmutable($domainArray['expirationDate']);
        $test = $this->domRepo->findOneBy(['name' => $domainArray['name']]);

        // Check if domain is already in database
        if (!is_null($test)) {
            $domain = $this->domRepo->findOneBy(['name' => $domainArray['name']]);

            $domain->setExpireAt($expirationDate);
            $domain->setIsHistory(false);
            $domain->setExpireAt($expirationDate);

            $this->em->persist($domain);
            $this->em->flush();

            return;
        }

        // Create a new Domain and persist to DB
        $domain = new Domain();

        $domain->setName($domainArray['name']);
        $domain->setCreatedAt($creationDate);
        $domain->setExpireAt($expirationDate);
        $domain->setIsToSuppress(false);
        $domain->setIsHistory(false);

        $this->em->persist($domain);
        $this->em->flush();
    }
}
