<?php

namespace App\Service;


/***
 * gestion des agendas en back
 */
use App\Repository\AgendaRepository;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use Doctrine\ORM\Query\Expr\Func;
use Symfony\Component\Validator\Constraints\DateTime;

Class AgendaGenerator{

    private $agendaRepository;
    private $d_dateDuJour;

    function __construct(AgendaRepository $agendaRepository){
        $this->agendaRepository = $agendaRepository;
        $this->d_dateDuJour = new \DateTime("now");
    }

    public function NextDateAgenda(){
        //reflechir pour grouper les utilisateurs
        $agendas = $this->agendaRepository->getAgendaAVenir();
        $arr_sortie = [];
        foreach($agendas as $agenda){
            $d_init = $agenda['heureDebut'];
            $recurrence = $agenda['recurrence'];
            $arr_date = $this->nextItemByRecurrence($recurrence,$d_init);
            // On ajoute les informations de l'agenda à chaque date
            foreach ($arr_date as &$date) {
                    $date = array_merge($date, [
                        'titre'   => $agenda['sujet'],
                        'corps'   => $agenda['corps'],
                        'lieu'    => $agenda['lieu'],
                        'duree'   => $agenda['duree'],
                        'nom'     => $agenda['nom'],
                        'prenom'  => $agenda['prenom'],
                    ]);
                }
            
                unset($date); // bonne pratique pour éviter un bug lié à la référence

            // On associe les résultats à l'id de l'agenda
            $arr_temp = [];
            $arr_temp[$agenda['id']] = $arr_date;
            $arr_sortie[] = $arr_temp;
        }
        return $arr_sortie;
    }

    public function nextItemByRecurrence($recurrence,$d_init,$d_close = null){
        //par default on met une date de fin à 7 mois
        $d_suivant = clone $d_init;
        $d_close = is_null($d_close) ? $d_init->add(new \DateInterval('P7M')) : $d_close;
        $arr_date = [];
        $arr_date[] = ["date" => clone $d_suivant];
        switch($recurrence){
            case "jour":
                $di = new \DateInterval('P1D');
                break;
            case "semaine":
                $di = new \DateInterval('P1W');
                break;
            case "deuxSemaines":
                $di = new \DateInterval('P2W');
                break;
            case "mois":
                $di = new \DateInterval('P1M');
                break;
            default:
                $d_close = $d_suivant;
                break;
        }

        for($i=1;$d_suivant < $d_close;$i++){
                $d_suivant = $d_suivant->add($di);
                $arr_date[] =["date" => clone $d_suivant];
        }

        return $arr_date;
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