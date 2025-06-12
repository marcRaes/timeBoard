<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WorkMonth;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkMonth>
 */
class WorkMonthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkMonth::class);
    }

    public function findAllForUser(User $user): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->orderBy('w.year', 'DESC')
            ->addOrderBy('w.month', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
