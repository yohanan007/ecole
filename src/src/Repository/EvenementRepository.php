<?php

namespace App\Repository;

use App\Entity\Evenement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 *
 * @method Evenement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evenement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evenement[]    findAll()
 * @method Evenement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Evenement $entity, bool $flush = true): void
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
    public function remove(Evenement $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Trouver tous les RDV à venir
     * 
     * @param \DateTimeInterface|null $fromDate
     * @return Evenement[]
     */
    public function findUpcoming(?\DateTimeInterface $fromDate = null): array
    {
        if ($fromDate === null) {
            $fromDate = new \DateTime('now');
        }

        return $this->createQueryBuilder('e')
            ->innerJoin('e.agenda', 'a')
            ->where('a.heureDebut >= :fromDate')
            ->setParameter('fromDate', $fromDate)
            ->orderBy('a.heureDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les RDV entre deux dates
     * 
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return Evenement[]
     */
    public function findBetweenDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.agenda', 'a')
            ->where('a.heureDebut BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('a.heureDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les RDV créés par un admin
     * 
     * @param App\Entity\Admin $admin
     * @return Evenement[]
     */
    public function findByAdmin($admin): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.adminCreateur = :admin')
            ->setParameter('admin', $admin)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les RDV d'un utilisateur
     * 
     * @param User $user
     * @return Evenement[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver les RDV avec une certaine récurrence
     * 
     * @param string $recurrence
     * @return Evenement[]
     */
    public function findByRecurrence(string $recurrence): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.recurrence = :recurrence')
            ->setParameter('recurrence', $recurrence)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compter les RDV à venir
     * 
     * @return int
     */
    public function countUpcoming(): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->innerJoin('e.agenda', 'a')
            ->where('a.heureDebut >= :now')
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouver un RDV avec ses détails complets
     * 
     * @param int $id
     * @return Evenement|null
     */
    public function findWithDetails(int $id): ?Evenement
    {
        return $this->createQueryBuilder('e')
            ->addSelect('a', 'u', 'admin')
            ->leftJoin('e.agenda', 'a')
            ->leftJoin('e.users', 'u')
            ->leftJoin('e.adminCreateur', 'admin')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // ✅ PARENT-CHILD PATTERN QUERIES

    /**
     * ✅ Récupérer toutes les occurrences futures (pas les parents)
     */
    public function findUpcomingOccurrences(\DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('e')
            ->addSelect('u', 'parent')
            ->leftJoin('e.users', 'u')
            ->leftJoin('e.parent', 'parent')
            ->where('e.parent IS NOT NULL')  // Seulement les occurrences
            ->andWhere('e.heureDebut >= :start')
            ->andWhere('e.heureDebut <= :end')
            ->orderBy('e.heureDebut', 'ASC')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    /**
     * ✅ Récupérer les occurrences d'un utilisateur
     */
    public function findUserUpcomingOccurrences(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.users', 'u')
            ->addSelect('parent')
            ->leftJoin('e.parent', 'parent')
            ->where('u = :user')
            ->andWhere('e.parent IS NOT NULL')  // Seulement les occurrences
            ->andWhere('e.heureDebut >= :now')
            ->orderBy('e.heureDebut', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getResult();
    }

    /**
     * ✅ Récupérer un événement parent avec toutes ses occurrences
     */
    public function findParentWithOccurrences(int $parentId): ?Evenement
    {
        return $this->createQueryBuilder('e')
            ->addSelect('occurrences', 'u')
            ->leftJoin('e.occurrences', 'occurrences')
            ->leftJoin('e.users', 'u')
            ->where('e.id = :id')
            ->andWhere('e.parent IS NULL')  // C'est un parent
            ->orderBy('occurrences.heureDebut', 'ASC')
            ->setParameter('id', $parentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * ✅ Récupérer tous les événements parents (récurrents)
     */
    public function findAllParents(): array
    {
        return $this->createQueryBuilder('e')
            ->addSelect('occurrences')
            ->leftJoin('e.occurrences', 'occurrences')
            ->where('e.parent IS NULL')
            ->andWhere('e.recurrence IS NOT NULL')
            ->andWhere('e.recurrence != :aucune')
            ->orderBy('e.heureDebut', 'DESC')
            ->setParameter('aucune', 'aucune')
            ->getQuery()
            ->getResult();
    }

    /**
     * ✅ Obtenir les statistiques
     */
    public function getStatistics(): array
    {
        $totalParents = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.parent IS NULL')
            ->andWhere('e.recurrence IS NOT NULL')
            ->andWhere('e.recurrence != :aucune')
            ->setParameter('aucune', 'aucune')
            ->getQuery()
            ->getSingleScalarResult();

        $totalOccurrences = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.parent IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $upcomingEvents = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.parent IS NOT NULL')
            ->andWhere('e.heureDebut >= :now')
            ->setParameter('now', (new \DateTime("now"))->sub(new \DateInterval('P6M')))
            ->getQuery()
            ->getSingleScalarResult();

        $recurringTypes = $this->createQueryBuilder('e')
            ->select('e.recurrence, COUNT(e.id) as count')
            ->where('e.parent IS NULL')
            ->andWhere('e.recurrence IS NOT NULL')
            ->groupBy('e.recurrence')
            ->getQuery()
            ->getResult();

        return [
            'totalParents' => (int) $totalParents,
            'totalOccurrences' => (int) $totalOccurrences,
            'upcomingEvents' => (int) $upcomingEvents,
            'recurringTypes' => array_reduce($recurringTypes, fn($carry, $item) => 
                array_merge($carry, [$item['recurrence'] => $item['count']]), []),
        ];
    }
}
