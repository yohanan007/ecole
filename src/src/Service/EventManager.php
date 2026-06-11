<?php

namespace App\Service;

use App\Entity\Evenement;
use App\Entity\Agenda;
use App\Entity\Admin;
use App\Entity\Eleve;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service pour gérer les opérations sur les RDV/Evenement
 */
class EventManager
{
    private EntityManagerInterface $entityManager;
    private EvenementRepository $evenementRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EvenementRepository $evenementRepository
    ) {
        $this->entityManager = $entityManager;
        $this->evenementRepository = $evenementRepository;
    }

    /**
     * Créer un nouveau RDV/Evenement
     * 
     * @param string $sujet
     * @param string|null $corps
     * @param string|null $lieu
     * @param \DateTime $startDateTime
     * @param int|null $durationMinutes
     * @param string|null $recurrence
     * @param Admin|null $admin
     * @return Evenement
     */
    public function createEvent(
        string $sujet,
        ?string $corps = null,
        ?string $lieu = null,
        \DateTime $startDateTime = null,
        ?int $durationMinutes = null,
        ?string $recurrence = null,
        ?Admin $admin = null
    ): Evenement {
        if ($startDateTime === null) {
            $startDateTime = new \DateTime();
        }

        $agenda = new Agenda();
        $agenda->setHeureDebut($startDateTime);

        $evenement = new Evenement();
        $evenement->setSujet($sujet);
        $evenement->setCorps($corps);
        $evenement->setLieu($lieu);
        $evenement->setDuree($durationMinutes);
        $evenement->setRecurrence($recurrence ?? 'aucune');
        $evenement->setCreatedAt(new \DateTime());
        $evenement->setAdminCreateur($admin);
        $evenement->setAgenda($agenda);

        $this->entityManager->persist($agenda);
        $this->entityManager->persist($evenement);
        $this->entityManager->flush();

        return $evenement;
    }

    /**
     * Ajouter un élève à un RDV
     * 
     * @param Evenement $evenement
     * @param Eleve $eleve
     * @return bool
     */
    public function addEleveToEvent(Evenement $evenement, Eleve $eleve): bool
    {
        // Vérifier que l'élève est validé
        if (!$eleve->isValidated()) {
            return false;
        }

        // Vérifier que l'élève a un User
        if (!$eleve->getUser()) {
            return false;
        }

        // Vérifier qu'il n'est pas déjà dans l'événement
        if ($evenement->getUsers()->contains($eleve->getUser())) {
            return false;
        }

        $evenement->addUser($eleve->getUser());
        $this->entityManager->persist($evenement);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Retirer un élève d'un RDV
     * 
     * @param Evenement $evenement
     * @param Eleve $eleve
     * @return bool
     */
    public function removeEleveFromEvent(Evenement $evenement, Eleve $eleve): bool
    {
        if (!$eleve->getUser()) {
            return false;
        }

        if (!$evenement->getUsers()->contains($eleve->getUser())) {
            return false;
        }

        $evenement->removeUser($eleve->getUser());
        $this->entityManager->persist($evenement);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Ajouter plusieurs élèves à la fois
     * 
     * @param Evenement $evenement
     * @param array $eleves Array of Eleve objects
     * @return int Nombre d'élèves ajoutés
     */
    public function addElevesToEvent(Evenement $evenement, array $eleves): int
    {
        $addedCount = 0;

        foreach ($eleves as $eleve) {
            if ($this->addEleveToEvent($evenement, $eleve)) {
                $addedCount++;
            }
        }

        return $addedCount;
    }

    /**
     * Dupliquer un RDV
     * 
     * @param Evenement $originalEvent
     * @param \DateTime|null $newStartDate
     * @return Evenement
     */
    public function duplicateEvent(
        Evenement $originalEvent,
        ?\DateTime $newStartDate = null
    ): Evenement {
        $newEventDate = $newStartDate ?? clone $originalEvent->getAgenda()->getHeureDebut();

        $newEvent = $this->createEvent(
            subject: $originalEvent->getSujet() . ' (copie)',
            corps: $originalEvent->getCorps(),
            lieu: $originalEvent->getLieu(),
            startDateTime: $newEventDate,
            durationMinutes: $originalEvent->getDuree(),
            recurrence: $originalEvent->getRecurrence(),
            admin: $originalEvent->getAdminCreateur()
        );

        // Copier les utilisateurs
        foreach ($originalEvent->getUsers() as $user) {
            $newEvent->addUser($user);
        }

        $this->entityManager->persist($newEvent);
        $this->entityManager->flush();

        return $newEvent;
    }

    /**
     * Vérifier s'il y a un conflit d'horaire pour un utilisateur
     * 
     * @param Evenement $evenement
     * @param \DateTime $startTime
     * @param int|null $durationMinutes
     * @return bool
     */
    public function hasTimeConflict(
        Evenement $evenement,
        \DateTime $startTime,
        ?int $durationMinutes = null
    ): bool {
        $endTime = clone $startTime;
        if ($durationMinutes) {
            $endTime->add(new \DateInterval('PT' . $durationMinutes . 'M'));
        }

        // Voir si un des utilisateurs de l'événement a un conflit
        foreach ($evenement->getUsers() as $user) {
            $userEvents = $this->evenementRepository->findByUser($user);

            foreach ($userEvents as $otherEvent) {
                if (!$otherEvent->getAgenda() || $otherEvent === $evenement) {
                    continue;
                }

                $otherStart = $otherEvent->getAgenda()->getHeureDebut();
                $otherEnd = clone $otherStart;
                if ($otherEvent->getDuree()) {
                    $otherEnd->add(new \DateInterval('PT' . $otherEvent->getDuree() . 'M'));
                }

                // Vérifier chevauchement
                if ($startTime < $otherEnd && $endTime > $otherStart) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Obtenir les statistiques d'un RDV
     * 
     * @param Evenement $evenement
     * @return array
     */
    public function getEventStats(Evenement $evenement): array
    {
        return [
            'id' => $evenement->getId(),
            'sujet' => $evenement->getSujet(),
            'createdAt' => $evenement->getCreatedAt(),
            'adminCreator' => $evenement->getAdminCreateur()?->__toString(),
            'totalParticipants' => $evenement->getUsers()->count(),
            'startTime' => $evenement->getAgenda()?->getHeureDebut(),
            'duration' => $evenement->getDuree(),
            'recurrence' => $evenement->getRecurrence(),
            'lieu' => $evenement->getLieu(),
        ];
    }

    /**
     * Supprimer un RDV
     * 
     * @param Evenement $evenement
     * @return bool
     */
    public function deleteEvent(Evenement $evenement): bool
    {
        try {
            $agenda = $evenement->getAgenda();
            
            // Supprimer tous les utilisateurs associés
            foreach ($evenement->getUsers() as $user) {
                $evenement->removeUser($user);
            }

            $this->entityManager->remove($evenement);
            
            // Supprimer l'agenda si elle n'a plus d'événements
            if ($agenda && count($agenda->getEvenements()) === 0) {
                $this->entityManager->remove($agenda);
            }

            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
