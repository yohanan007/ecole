<?php

namespace App\Service;

use App\Repository\EvenementRepository;
use App\Repository\EleveRepository;
use App\Repository\ClasseEleveRepository;
use App\Entity\Evenement;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class AgendaGenerator
{
    private $d_dateDepartAgenda;

    public function __construct(
        private EvenementRepository $evenementRepository,
        private EleveRepository $eleveRepository,
        private ClasseEleveRepository $classeEleveRepository,
        private EntityManagerInterface $em
    ) {
        $this->d_dateDepartAgenda = (new DateTime("now"))->sub(new \DateInterval('P6M'));
    }

    /**
     * ✅ Crée un événement parent récurrent + génère les occurrences enfants
     *
     * @param Evenement $parentEvent Événement parent (doit avoir recurrence défini)
     * @return void
     */
    public function createRecurringEvent(Evenement $parentEvent): void
    {
        if (!$parentEvent->isParent()) {
            throw new \InvalidArgumentException('Event must be a parent with recurrence set');
        }

        // Générer les dates d'occurrences
        $dates = $this->generateRecurrenceDates(
            $parentEvent->getHeureDebut(),
            $parentEvent->getRecurrence(),
            $parentEvent->getRecurrenceEnd() ?? (clone $parentEvent->getHeureDebut())->add(new \DateInterval('P7M'))
        );

        // Créer les occurrences enfants
        foreach ($dates as $date) {
            $occurrence = new Evenement();
            $occurrence->setSujet($parentEvent->getSujet());
            $occurrence->setHeureDebut($date['date']);
            $occurrence->setCorps($parentEvent->getCorps());
            $occurrence->setLieu($parentEvent->getLieu());
            $occurrence->setDuree($parentEvent->getDuree());
            $occurrence->setAdminCreateur($parentEvent->getAdminCreateur());
            $occurrence->setCreatedAt(new DateTime());
            $occurrence->setParent($parentEvent);

            // Copier les utilisateurs
            foreach ($parentEvent->getUsers() as $user) {
                $occurrence->addUser($user);
            }

            $parentEvent->addOccurrence($occurrence);
            $this->em->persist($occurrence);
        }

        $this->em->persist($parentEvent);
        $this->em->flush();
    }

    /**
     * ✅ Met à jour un événement parent + régénère les occurrences si nécessaire
     */
    public function updateRecurringEvent(Evenement $parentEvent): void
    {
        if (!$parentEvent->isParent()) {
            throw new \InvalidArgumentException('Event must be a parent with recurrence set');
        }

        // Supprimer les anciennes occurrences
        foreach ($parentEvent->getOccurrences() as $occurrence) {
            $this->em->remove($occurrence);
        }

        // Régénérer les nouvelles
        $this->createRecurringEvent($parentEvent);
    }

    /**
     * ✅ Met à jour une occurrence spécifique (seulement celle-ci)
     */
    public function updateOccurrence(Evenement $occurrence, array $data): void
    {
        if (!$occurrence->isOccurrence()) {
            throw new \InvalidArgumentException('Event must be an occurrence');
        }

        // Mettre à jour uniquement les champs fournis
        if (isset($data['sujet'])) $occurrence->setSujet($data['sujet']);
        if (isset($data['heureDebut'])) $occurrence->setHeureDebut(new DateTime($data['heureDebut']));
        if (isset($data['corps'])) $occurrence->setCorps($data['corps']);
        if (isset($data['lieu'])) $occurrence->setLieu($data['lieu']);
        if (isset($data['duree'])) $occurrence->setDuree($data['duree']);

        $this->em->persist($occurrence);
        $this->em->flush();
    }

    /**
     * ✅ Supprime une occurrence spécifique
     */
    public function deleteOccurrence(Evenement $occurrence): void
    {
        if (!$occurrence->isOccurrence()) {
            throw new \InvalidArgumentException('Event must be an occurrence');
        }

        $this->em->remove($occurrence);
        $this->em->flush();
    }

    /**
     * ✅ Récupère les événements à venir (parents + occurrences futures)
     */
    public function getUpcomingEvents(int $monthsToGenerate = 7): array
    {
        $d_close = (new DateTime("now"))->add(new \DateInterval('P' . $monthsToGenerate . 'M'));

        // Récupérer TOUTES les occurrences futures (pas les parents)
        $events = $this->evenementRepository->findUpcomingOccurrences(
            $this->d_dateDepartAgenda,
            $d_close
        );

        return $this->formatEvents($events);
    }

    /**
     * ✅ Récupère l'agenda pour un utilisateur spécifique
     */
    public function getAgendaForUser(User $user): array
    {
        // Récupérer toutes les occurrences de cet utilisateur
        $events = $this->evenementRepository->findUserUpcomingOccurrences($user);

        return $this->formatEvents($events);
    }

    /**
     * ✅ Vérifie si un utilisateur est libre à une date/heure
     */
    public function isUserAvailable(User $user, DateTime $dateTime): bool
    {
        $dateStr = $dateTime->format('Y-m-d');

        $events = $this->evenementRepository->findUserUpcomingOccurrences($user);

        foreach ($events as $event) {
            $eventDate = $event->getHeureDebut()->format('Y-m-d');
            if ($eventDate === $dateStr) {
                return false;
            }
        }

        return true;
    }

    /**
     * ✅ Obtient les prochains créneaux libres
     */
    public function getNextAvailableSlots(User $user, int $daysAhead = 30): array
    {
        $userEvents = $this->getAgendaForUser($user);
        $busyDates = [];

        foreach ($userEvents as $event) {
            // Format FullCalendar: 'start' contient la chaîne ISO
            if (isset($event['start'])) {
                $busyDates[] = substr($event['start'], 0, 10);
            }
        }

        $availableDates = [];
        $startDate = new DateTime('now');
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P' . $daysAhead . 'D'));

        while ($startDate <= $endDate) {
            $dateStr = $startDate->format('Y-m-d');
            if (!in_array($dateStr, $busyDates)) {
                $availableDates[] = $dateStr;
            }
            $startDate->add(new \DateInterval('P1D'));
        }

        return $availableDates;
    }

    /**
     * ✅ Obtient les statistiques sur les événements
     */
    public function getStatistics(): array
    {
        $stats = $this->evenementRepository->getStatistics();

        return [
            'totalParents' => $stats['totalParents'] ?? 0,
            'totalOccurrences' => $stats['totalOccurrences'] ?? 0,
            'upcomingEvents' => $stats['upcomingEvents'] ?? 0,
            'recurringTypes' => $stats['recurringTypes'] ?? [],
        ];
    }

    /**
     * Génère les dates en fonction de la récurrence
     */
    private function generateRecurrenceDates(
        DateTime $start,
        string $recurrence,
        DateTime $end
    ): array {
        if ($recurrence === 'aucune' || empty($recurrence)) {
            return [["date" => $start]];
        }

        $dates = [["date" => clone $start]];
        $current = clone $start;
        $interval = $this->getDateIntervalByRecurrence($recurrence);

        while ($current < $end) {
            $current = $current->add($interval);
            if ($current <= $end) {
                $dates[] = ["date" => clone $current];
            }
        }

        return $dates;
    }

    /**
     * Retourne l'intervalle selon la récurrence
     */
    private function getDateIntervalByRecurrence(string $recurrence): \DateInterval
    {
        return match ($recurrence) {
            "jour" => new \DateInterval('P1D'),
            "semaine" => new \DateInterval('P1W'),
            "deuxSemaines" => new \DateInterval('P2W'),
            "mois" => new \DateInterval('P1M'),
            default => new \DateInterval('P0D'),
        };
    }

    /**
     * Formate les événements pour le frontend (format FullCalendar)
     * 
     * @return array[] Array of events formatted for FullCalendar
     */
    private function formatEvents(array $events): array
    {
        $arr_sortie = [];

        foreach ($events as $event) {
            $eventId = $event->getId();
            $heureDebut = $event->getHeureDebut();
            
            if (!$heureDebut) {
                continue; // Skip events without start time
            }

            // Calculer l'heure de fin basée sur la durée (en minutes)
            $heureFin = clone $heureDebut;
            if ($event->getDuree()) {
                $heureFin->modify('+' . $event->getDuree() . ' minutes');
            }

            // Récupérer les élèves et leurs classes
            $eleveData = $this->getEleveDataFromUsers($event->getUsers());

            // Déterminer la classe (première classe si elle existe, sinon 'aucune')
            $classe = $eleveData['classe'] ?? 'aucune';

            $arr_sortie[] = [
                'title' => $event->getSujet() ?? 'Sans titre',
                'start' => $heureDebut->format('Y-m-d\TH:i:s'),
                'end' => $heureFin->format('Y-m-d\TH:i:s'),
                'extendedProps' => [
                    'id' => $eventId,
                    'lieu' => $event->getLieu() ?? '',
                    'description' => $event->getCorps() ?? '',
                    'classe' => $classe,
                    'eleves' => $eleveData['eleves'] ?? [],
                    'parentId' => $event->getParent()?->getId(),
                    'recurrence' => $event->getParent() ? 'child' : $event->getRecurrence(),
                ]
            ];
        }

        return $arr_sortie;
    }

    /**
     * Extrait les données des élèves à partir des utilisateurs associés à l'événement
     * 
     * @return array Contient 'eleves' (array) et 'classe' (string)
     */
    private function getEleveDataFromUsers($users): array
    {
        $eleves = [];
        $classes = [];

        foreach ($users as $user) {
            // Chercher l'élève associé à cet utilisateur
            $eleve = $this->eleveRepository->findOneBy(['user' => $user]);
            
            if ($eleve) {
                $eleves[] = [
                    'id' => $eleve->getId(),
                    'nom' => $eleve->getNom() ?? '',
                    'prenom' => $eleve->getPrenom() ?? ''
                ];

                // Récupérer la classe actuelle de l'élève
                $classeEleve = $this->classeEleveRepository->findOneBy(
                    ['Eleve' => $eleve],
                    ['DateValide' => 'DESC']
                );

                if ($classeEleve) {
                    $classe = $classeEleve->getClasse();
                    $classes[] = $classe->getNom() ?? 'Sans nom';
                }
            }
        }

        return [
            'eleves' => $eleves,
            'classe' => !empty($classes) ? $classes[0] : 'aucune'
        ];
    }
}