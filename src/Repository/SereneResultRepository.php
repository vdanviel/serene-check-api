<?php

namespace App\Repository;

use App\Entity\SereneResult;

use App\Entity\User;
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
    public function listAllUserDialogs(int $limit, int $offset): array
    {
        $entityManager = $this->getEntityManager();

        //https://symfony.com/doc/current/doctrine/associations.html#joining-related-records
        /*SELECT p, c
            FROM App\Entity\Product p
            INNER JOIN p.category c
            WHERE p.id = :id*/
        $query = $entityManager->createQuery(
            'SELECT sr, usr.id, usr.name FROM App\Entity\SereneResult sr INNER JOIN sr.user usr ORDER BY sr.created_at DESC'
        )->setMaxResults($limit)->setFirstResult($offset);

        // returns an array of Product objects
        return $query->getArrayResult();

    }

    public function listUserDialogs(User $user, int $limit, int $offset): array
    {

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT table FROM App\Entity\SereneResult table WHERE table.user = :id ORDER BY table.created_at DESC'
        )->setParameter(':id', $user->getId())->setMaxResults($limit)->setFirstResult($offset);

        // returns an array of Product objects
        return $query->getArrayResult();

    }

    public function getDialog(int $id): array
    {
        //https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/query-builder.html#the-querybuilder
        $qb = $this->createQueryBuilder('sr')
            ->where('sr.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        $query = $qb->getQuery();

        return $query->getArrayResult();

    }
}
