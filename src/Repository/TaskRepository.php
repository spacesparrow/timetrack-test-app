<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    private const TP_SELF = 't';

    /**
     * TaskRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findByUser(User $user): Collection
    {
        $qb = $this->createQueryBuilder(self::TP_SELF);
        $qb->where(self::TP_SELF.'.user = :user');
        $qb->setParameter('user', $user);
        $qb->orderBy(self::TP_SELF.'.createdDate', 'DESC');

        $tasks = $qb->getQuery()->getResult();

        return new ArrayCollection($tasks);
    }

    public function findUserTasksFilteredByDateRange(User $user, DateTime $startDate, DateTime $endDate): Collection
    {
        $qb = $this->createQueryBuilder(self::TP_SELF);
        $qb->where(self::TP_SELF.'.user = :user');
        $qb->andWhere(self::TP_SELF.'.createdDate BETWEEN :startDate AND :endDate');
        $qb->setParameter('user', $user);
        $qb->setParameter('startDate', $startDate);
        $qb->setParameter('endDate', $endDate);
        $qb->orderBy(self::TP_SELF.'.createdDate', 'DESC');

        $tasks = $qb->getQuery()->getResult();

        return new ArrayCollection($tasks);
    }
}
