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

    /**
     * Function that returns the Domains that expire between now and the given date.
     * @param \DateTimeImmutable $date
     * @return mixed
     */
    public function getExpireSoon(\DateTimeImmutable $date): mixed
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

    /**
     * Function that return the number of Domains that expire for the current month
     * and the 11 next, grouped by month.
     * @param \DateTimeImmutable $date
     * @return array
     */
    public function getCountExpire(\DateTimeImmutable $date): array
    {
        // Set date to the 1st of the month
        $dateD1 = $date->setDate($date->format('Y'), $date->format('m'), 1);

        for ($i = 0; $i <= 11; $i++) {
             $result = $this->createQueryBuilder('d')
                ->select('count(d.id)')
                ->where('d.expireAt < :monthEnd')
                ->andWhere('d.expireAt >= :monthStart')
                ->setParameter('monthEnd', $dateD1->add(new \DateInterval('P1M')))
                ->setParameter('monthStart', $dateD1)
                ->getQuery()
                ->getResult()
            ;
            $year[$i] = $result[0][1];
            $dateD1 = $dateD1->add(new \DateInterval('P1M'));
        }

        return $year;
    }

    public function getIsToSuppressToday(): array
    {
        $dayStart = new \DateTimeImmutable()->setTime(0, 0, 0);
        $dayEnd = $dayStart->add(new \DateInterval('P1D'));

        return $this->createQueryBuilder('d')
            ->where('d.isToSuppress = 1')
            ->andWhere('d.expireAt <= :dateEnd')
            ->andWhere('d.expireAt >= :dateStart')
            ->setParameter('dateEnd', $dayEnd)
            ->setParameter('dateStart', $dayStart)
            ->orderBy('d.expireAt', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
