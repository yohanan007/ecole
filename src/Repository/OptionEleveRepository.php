<?php

namespace App\Repository;

use App\Entity\OptionEleve;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OptionEleve>
 *
 * @method OptionEleve|null find($id, $lockMode = null, $lockVersion = null)
 * @method OptionEleve|null findOneBy(array $criteria, array $orderBy = null)
 * @method OptionEleve[]    findAll()
 * @method OptionEleve[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionEleveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OptionEleve::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(OptionEleve $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(OptionEleve $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return OptionEleve[] Returns an array of OptionEleve objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OptionEleve
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
