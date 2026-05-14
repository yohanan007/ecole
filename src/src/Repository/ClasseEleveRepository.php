<?php

namespace App\Repository;

use App\Entity\ClasseEleve;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClasseEleve>
 *
 * @method ClasseEleve|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClasseEleve|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClasseEleve[]    findAll()
 * @method ClasseEleve[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClasseEleveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClasseEleve::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ClasseEleve $entity, bool $flush = true): void
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
    public function remove(ClasseEleve $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Récupère la dernière classe pour chaque élève en une seule requête
     * Retourne un array avec la structure: [eleve_id => ClasseEleve]
     */
    public function findLatestClassesByEleves(array $eleves): array
    {
        if (empty($eleves)) {
            return [];
        }

        // Récupérer tous les ClasseEleve triés par DateValide DESC
        $allClasseEleves = $this->createQueryBuilder('ce')
            ->innerJoin('ce.Eleve', 'e')
            ->where('e IN (:eleves)')
            ->setParameter('eleves', $eleves)
            ->orderBy('ce.DateValide', 'DESC')
            ->getQuery()
            ->getResult();

        // Retourner uniquement le premier (plus récent) pour chaque élève
        $result = [];
        foreach ($allClasseEleves as $classeEleve) {
            $eleveId = $classeEleve->getEleve()->getId();
            if (!isset($result[$eleveId])) {
                $result[$eleveId] = $classeEleve;
            }
        }

        return $result;
    }

    // /**
    //  * @return ClasseEleve[] Returns an array of ClasseEleve objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClasseEleve
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
