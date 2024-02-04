<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
//use App\Repository\EvenementRepository;
use App\Repository\AgendaRepository;
use App\Entity\Evenement;
use App\Entity\Agenda;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;

class AgendaController extends AbstractController
{
    /**
     * @Route("/agenda",methods={"GET"}, name="app_agenda")
     */
    public function index(AgendaRepository $agendaRepository): Response
    {
        $arr_agenda = $agendaRepository->getAgendaAVenir();

        $str_day = date("d");
        $str_month = date("m");
        $str_year = date("Y");
        return $this->render('agenda/index.html.twig', [
            'controller_name' => 'AgendaController',
            'day'=>$str_day,
            'month'=>$str_month,
            'year'=>$str_year,
            'format'=>'month',
            'agenda'=>json_encode($arr_agenda)
        ]);
    }

    /**
     * @Route("/agenda/{str_time}", methods={"GET"}, name="app_agenda_time")
     */
    public function indexTime($str_time): Response
    {
        if($str_time !== ""){
        $date = new \DateTime();
        $date->setTimestamp(intval($str_time)/1000);
        $str_day = $date->format("d");
        $str_month = $date->format("m");
        $str_year = $date->format("Y");
        return $this->render('agenda/index.html.twig', [
            'controller_name' => 'AgendaController',
            'day'=>$str_day,
            'month'=>$str_month,
            'year'=>$str_year,
            'format'=>'month'
        ]);
        }
    }


    /**
     * @Route("/agenda",methods={"POST"}, name="app_agenda_post")
     */
    public function createTime(Request $obj_request, ManagerRegistry $obj_doctrine,) : Response
    {
        /*
        réccupération des éléments envoyé
        */
        $arr_data = $obj_request->toArray();
        $str_crsfToken = $arr_data['create-time'];
        $response = new JsonResponse();
        if ($this->isCsrfTokenValid('create-time', $str_crsfToken)){
            $obj_entityManager = $obj_doctrine->getManager();
            $str_time_debut = $arr_data['secondeDebut'];
            $str_time_fin = $arr_data['secondeFin'];

            $str_objet = $arr_data['data'];
            
            $obj_agenda = new Agenda();
            $obj_evenement = new Evenement();

            $obj_evenement->setLieu("");
            $obj_evenement->setCorps($str_objet);
            $obj_evenement->setSujet("");

            $date = new \DateTime();
            $date->setTimestamp(intval($str_time_debut)/1000);
            $obj_agenda->setHeureDebut($date);
            $date = new \DateTime();
            $date->setTimestamp(intval($str_time_fin)/1000);
            $obj_entityManager->persist($obj_evenement);
            $obj_agenda->addEvenement($obj_evenement);
            $obj_entityManager->persist($obj_agenda);
            $obj_entityManager->flush();
            
            $response->setData(["ok"=>"ok"]);
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
