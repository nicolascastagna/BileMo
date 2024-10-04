<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * findPaginatedUsersByCustomer
     *
     * @param int $customerId
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function findPaginatedUsersByCustomer(int $customerId, int $page = 1, int $limit = 10): Paginator
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.customer = :customerId')
            ->setParameter('customerId', $customerId)
            ->orderBy('u.lastname', 'ASC')
            ->getQuery();

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }
}
