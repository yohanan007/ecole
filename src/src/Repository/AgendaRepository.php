<?php

namespace App\Repository;

use App\Entity\Agenda;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Agenda>
 *
 * @method Agenda|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agenda|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agenda[]    findAll()
 * @method Agenda[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agenda::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Agenda $entity, bool $flush = true): void
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
    public function remove(Agenda $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

public function getAgendaAVenir($d_debut = null, $d_fin = null)
{
    if (is_null($d_debut)) {
        $d_debutTemp = new \DateTime('now');
        $d_debutTemp->setTime(0, 0, 0, 0);

        $d_debut = clone $d_debutTemp;
        $d_debut->sub(new \DateInterval('P6M'));
        
    }

    if (is_null($d_fin)) {
        $d_fin = clone $d_debutTemp;
        $d_fin->add(new \DateInterval('P9M')); // Limite à 9 mois max
    }

    $qb = $this->createQueryBuilder('a');

    $qb->select('
            DISTINCT a.id,
            a.heureDebut,
            e.id AS evenement_id,
            e.sujet,
            e.recurrence,
            e.corps,
            e.lieu,
            e.duree,
            el.id AS eleve_id,
            el.nom AS eleve_nom,
            el.prenom AS eleve_prenom
        ')
        ->innerJoin('a.evenements', 'e')
        ->leftJoin('e.users', 'u')
        ->leftJoin('App\Entity\Eleve', 'el', 'WITH', 'el.user = u.id')
        ->where('a.heureDebut >= :date_debut')
        ->andWhere('a.heureDebut <= :date_fin')
        ->orderBy('a.heureDebut', 'ASC')
        ->setParameter('date_debut', $d_debut)
        ->setParameter('date_fin', $d_fin);

    return $qb->getQuery()->getResult();
}
    // /**
    //  * @return Agenda[] Returns an array of Agenda objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Agenda
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
