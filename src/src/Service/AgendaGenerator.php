<?php

namespace App\Service;

use App\Repository\AgendaRepository;

class AgendaGenerator
{
    private $agendaRepository;
    private $d_dateDuJour;

    public function __construct(AgendaRepository $agendaRepository)
    {
        $this->agendaRepository = $agendaRepository;
        $this->d_dateDuJour = new \DateTime("now");
    }

    /**
     * Génère les prochaines dates pour les agendas à venir.
     *
     * @return array
     */
    public function NextDateAgenda(): array
    {
        $agendas = $this->agendaRepository->getAgendaAVenir();
        $arr_sortie = [];
        

        foreach ($agendas as $agenda) {

            // Génère les dates en fonction de la récurrence
            $d_init = $agenda['heureDebut'];
            $recurrence = $agenda['recurrence'];
            $arr_date = $this->nextItemByRecurrence($recurrence, $d_init);
            $eventId = $agenda['evenement_id'];
        


            foreach ($arr_date as $date) {
                if (!isset($arr_sortie[$eventId])) {
                    $arr_sortie[$eventId] = [
                        'title' => $agenda['sujet'],
                        'recurrence' => $agenda['recurrence'],
                        'corps' => $agenda['corps'],
                        'lieu' => $agenda['lieu'],
                        'duree' => $agenda['duree'],
                        'dates' => [],
                        'eleves' => [],
                    ];
                }
                if ($date['date'] < $this->d_dateDuJour) {
                    continue; // Ignore les dates passées
                }

                $arr_sortie[$eventId]['dates'][] = [
                    'date' => $date['date']->format('Y-m-d H:i:s'),
                    'date_init' => $d_init->format('Y-m-d H:i:s'),
                ];



                if (!in_array([
                    'id' => $agenda['eleve_id'],
                    'nom' => $agenda['eleve_nom'],
                    'prenom' => $agenda['eleve_prenom'],
                ], $arr_sortie[$eventId]['eleves'])) {

                    $arr_sortie[$eventId]['eleves'][] = [
                        'id' => $agenda['eleve_id'],
                        'nom' => $agenda['eleve_nom'],
                        'prenom' => $agenda['eleve_prenom'],
                    ];
                }
            }
        }
        
        return array_values($arr_sortie);
    }

    /**
     * Génère les prochaines occurrences d'une date en fonction de la récurrence.
     *
     * @param string $recurrence
     * @param \DateTime $d_init
     * @param \DateTime|null $d_close
     * @return array
     */
    public function nextItemByRecurrence(string $recurrence, \DateTime $d_init, ?\DateTime $d_close = null): array
    {
        // Par défaut, on met une date de fin à 7 mois
        $d_suivant = clone $d_init;
        $d_close = $d_close ?? (clone $d_init)->add(new \DateInterval('P7M'));
        $arr_date = [["date" => clone $d_suivant]];

        // Détermine l'intervalle en fonction de la récurrence
        $di = $this->getDateIntervalByRecurrence($recurrence);

        // Génère les dates jusqu'à la date de fin
        while ($d_suivant < $d_close) {
            $d_suivant = $d_suivant->add($di);
            $arr_date[] = ["date" => clone $d_suivant];
        }

        return $arr_date;
    }

    /**
     * Retourne l'intervalle de temps en fonction de la récurrence.
     *
     * @param string $recurrence
     * @return \DateInterval
     */
    private function getDateIntervalByRecurrence(string $recurrence): \DateInterval
    {
        return match ($recurrence) {
            "jour" => new \DateInterval('P1D'),
            "semaine" => new \DateInterval('P1W'),
            "deuxSemaines" => new \DateInterval('P2W'),
            "mois" => new \DateInterval('P1M'),
            default => new \DateInterval('P0D'), // Aucun intervalle
        };
    }

    public function UserIsFres($user,$agenda){
        return true;
    }

    public function UserIsFresForCreneau($user, $agenda){
        return true;
    }

    public function NextDateForUser($user,$agenda){

        return true;
    }

    public function TimeFreeForUser($user, $agenda){
        return true;
    }

}