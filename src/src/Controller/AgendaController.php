<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\AgendaRepository;
use App\Entity\Evenement;
use App\Entity\Agenda;
use App\Repository\ClasseRepository;
use App\Repository\EleveRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\AgendaGenerator;

class AgendaController extends AbstractController
{
    #[Route('/agenda', methods: ['GET'], name: 'app_agenda')]
    public function index(AgendaGenerator $agendaGenerator): Response
    {
        //todo : à retravailler, format pas encore au top
        $arr_agenda = $agendaGenerator->NextDateAgenda();
        return $this->render('agenda/index.html.twig', [
            'controller_name' => 'AgendaController',
            'agenda' => json_encode($arr_agenda)
        ]);
    }

    #[Route('/agenda/create', methods: ['GET'], name: 'app_agenda_create')]
    public function createview(Request $obj_request,ClasseRepository $classeRepository, EleveRepository $eleveRepository): Response
    {

        $eleves = $eleveRepository->findAll();
        $classes = $classeRepository->findAll();

        return $this->render('agenda/create.html.twig', [
            'eleves' => $eleves,
            'classes' => $classes,
            'controller_name' => 'AgendaController'
        ]);
    }

    #[Route('/agenda/{str_time}', methods: ['GET'], name: 'app_agenda_time')]
    public function indexTime(string $str_time, AgendaRepository $agendaRepository): Response
    {
        if ($str_time !== "") {
            $arr_agenda = $agendaRepository->getAgendaAVenir();
            $date = new \DateTime();
            $date->setTimestamp(intval($str_time) / 1000);
            $str_day = $date->format("d");
            $str_month = $date->format("m");
            $str_year = $date->format("Y");

            return $this->render('agenda/index.html.twig', [
                'controller_name' => 'AgendaController',
                'day' => $str_day,
                'month' => $str_month,
                'year' => $str_year,
                'format' => 'month',
                'agenda' => json_encode($arr_agenda)
            ]);
        }

        return new Response('Invalid time', Response::HTTP_BAD_REQUEST);
    }

    #[Route('/agenda', methods: ['POST'], name: 'app_agenda_post')]
    public function createTime(Request $obj_request, ManagerRegistry $obj_doctrine, EleveRepository $obj_eleveRepository): Response
    {
        $str_crsfToken = $obj_request->get('create-time');
        $response = new JsonResponse();

        if ($this->isCsrfTokenValid('create-time', $str_crsfToken)) {
            $obj_entityManager = $obj_doctrine->getManager();
            $str_d_debut = $obj_request->get('dateDebut');
            $str_d_fin = $obj_request->get('dateFin');

            $str_h_debut = $obj_request->get('heureDebut');
            $str_h_fin = $obj_request->get('heureFin');

            $datetime_debut_str = $str_d_debut . ' ' . $str_h_debut . ':00';
            $datetime_fin_str   = $str_d_fin   . ' ' . $str_h_fin   . ':00';

            // Création d'objets DateTime
            $dt_debut = new \DateTime($datetime_debut_str);
            $dt_fin   = new \DateTime($datetime_fin_str);

            //calcul de la duree 
            $duree = $dt_fin->getTimestamp() - $dt_debut->getTimestamp();


            $str_objet = $obj_request->get('data');

            $str_idEleve = $obj_request->get('eleve');
            $str_idClasse = $obj_request->get('classe');

            $str_lieu = $obj_request->get('lieu');

            $str_sujet = $obj_request->get('sujet');

            $str_reccurrence = $obj_request->get('reccurence');

            if($str_idClasse !== "-1"){
                $arr_eleve = ($str_idEleve === "-1") ? $obj_eleveRepository->findEleveByClasse($str_idClasse) : [$obj_eleveRepository->find($str_idEleve)];
            }else{
                $arr_eleve = $obj_eleveRepository->findAll();
            }

            if(count($arr_eleve)>0){
                $obj_agenda = new Agenda();
                $obj_evenement = new Evenement();
                $obj_evenement->setSujet($str_sujet);
                $obj_evenement->setCorps($str_objet);

                $obj_evenement->setLieu($str_lieu);
                    
                $obj_evenement->setDuree($duree);
                $obj_evenement->setRecurrence($str_reccurrence);
        
                $obj_agenda->setHeureDebut($dt_debut);
                foreach($arr_eleve as $eleve){
                    $obj_evenement->addUser($eleve->getUser());
                }
                $obj_entityManager->persist($obj_evenement);
                $obj_agenda->addEvenement($obj_evenement);
                $obj_entityManager->persist($obj_agenda);
            }


            $obj_entityManager->flush();

            $response->setData(["ok" => "ok"]);
        } else {
            $response->setData(["error" => "Invalid CSRF token"]);
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


}
