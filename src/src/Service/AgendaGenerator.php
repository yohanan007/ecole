<?php

namespace App\Service;


/***
 * gestion des agendas en back
 */
use App\Repository\AgendaRepository;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;

Class AgendaGenerator{

    private $agendaRepository;
    private $d_dateDuJour;

    function __construct(AgendaRepository $agendaRepository){
        $this->agendaRepository = $agendaRepository;
        $this->d_dateDuJour = new \DateTime("now");
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