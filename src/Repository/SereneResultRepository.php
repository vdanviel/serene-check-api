<?php

namespace App\Repository;

use App\Entity\SereneResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SereneResult>
 */
class SereneResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SereneResult::class);
    }

    //    /**
    //     * @return SereneResult[] Returns an array of SereneResult objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?SereneResult
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    //https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository
    //https://www.doctrine-project.org/projects/doctrine-orm/en/3.2/cookbook/dql-custom-walkers.html#:~:text=The%20Doctrine%20Query%20Language%20(DQL,the%20database%20using%20your%20entities.
    /**
     * @return SereneResult[]
     */
    public function findAllWithLimit(int $limit): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT sr FROM App\Entity\SereneResult sr ORDER BY sr.id DESC'
        )->setMaxResults($limit);

        // returns an array of Product objects
        return $query->execute();
    }

}
