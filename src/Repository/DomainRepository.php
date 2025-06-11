<?php

namespace App\Repository;

use App\Entity\Domain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Domain>
 */
class DomainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Domain::class);
    }

    public function getExpireSoon(\DateTimeImmutable $date)
    {
        return $this->createQueryBuilder('d')
            ->where('d.expireAt <= :date')
            ->andWhere('d.expireAt >= :dateNow')
            ->setParameter('date', $date)
            ->setParameter('dateNow', new \DateTimeImmutable())
            ->orderBy('d.expireAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
