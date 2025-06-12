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

    public function getCountExpire(\DateTimeImmutable $date)
    {
        $date->setDate($date->format('Y'), $date->format('m'), 1);
        $year = array(
            1 => null,
            2 => null,
            3 => null,
            4 => null,
            5 => null,
            6 => null,
            7 => null,
            8 => null,
            9 => null,
            10 => null,
            11 => null,
            12 => null,
        );
        for ($i = 1; $i <= 12; $i++) {
            if ($date->format('m') === $i) {
                $date->add(new \DateInterval('P1Y'));
            }
            $year[$i] = $this->createQueryBuilder('d')
                ->select('count(d.id)')
                ->where('d.expireAt < :monthEnd')
                ->andWhere('d.expireAt >= :monthStart')
                ->setParameter('monthEnd', $date->add(new \DateInterval('P1M')))
                ->setParameter('monthStart', $date)
                ->getQuery()
                ->getResult()
            ;
            $date = $date->add(new \DateInterval('P1M'));
        }
        return $year;
    }
}
