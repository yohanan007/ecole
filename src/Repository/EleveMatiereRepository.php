<?php

namespace App\Repository;

use App\Entity\EleveMatiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EleveMatiere>
 *
 * @method EleveMatiere|null find($id, $lockMode = null, $lockVersion = null)
 * @method EleveMatiere|null findOneBy(array $criteria, array $orderBy = null)
 * @method EleveMatiere[]    findAll()
 * @method EleveMatiere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EleveMatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EleveMatiere::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EleveMatiere $entity, bool $flush = true): void
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
    public function remove(EleveMatiere $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return EleveMatiere[] Returns an array of EleveMatiere objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EleveMatiere
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
