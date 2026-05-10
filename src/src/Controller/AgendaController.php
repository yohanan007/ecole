<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\AgendaRepository;
use App\Repository\EvenementRepository;
use App\Repository\EleveRepository;
use App\Repository\ClasseRepository;
use App\Repository\AdminRepository;
use App\Entity\Evenement;
use App\Entity\Agenda;
use App\Entity\Eleve;
use App\Form\EvenementType;
use App\Service\AgendaGenerator;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/agenda')]
class AgendaController extends AbstractController
{
    private AgendaGenerator $agendaGenerator;
    private EvenementRepository $evenementRepository;

    public function __construct(
        AgendaGenerator $agendaGenerator,
        EvenementRepository $evenementRepository
    ) {
        $this->agendaGenerator = $agendaGenerator;
        $this->evenementRepository = $evenementRepository;
    }

    /**
     * Afficher l'agenda principal
     */
    #[Route('/', name: 'app_agenda', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        // ✅ Récupérer les événements à venir (occurrences uniquement)
        $events = $this->agendaGenerator->getUpcomingEvents();
        $statistics = $this->agendaGenerator->getStatistics();

        return $this->render('agenda/index.html.twig', [
            'agenda' => json_encode($events),
            'agendaArray' => $events,
            'statistics' => $statistics,
            'controller_name' => 'AgendaController',
        ]);
    }

    /**
     * Afficher l'agenda pour une date spécifique
     */
    #[Route('/date/{timestamp}', name: 'app_agenda_date', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function indexByDate(string $timestamp, EvenementRepository $evenementRepository): Response
    {
        if (empty($timestamp) || !is_numeric($timestamp)) {
            return new Response('Invalid timestamp', Response::HTTP_BAD_REQUEST);
        }

        $date = new \DateTime();
        $date->setTimestamp(intval($timestamp) / 1000);

        $str_day = $date->format("d");
        $str_month = $date->format("m");
        $str_year = $date->format("Y");

        $arr_agenda = $this->agendaGenerator->getUpcomingEvents();
        $filteredAgenda = $this->filterAgendaByDate($arr_agenda, $date);

        return $this->render('agenda/index.html.twig', [
            'agenda' => json_encode($filteredAgenda),
            'agendaArray' => $filteredAgenda,
            'day' => $str_day,
            'month' => $str_month,
            'year' => $str_year,
            'format' => 'day',
            'controller_name' => 'AgendaController',
        ]);
    }

    /**
     * Voir l'agenda personnel (pour l'utilisateur connecté)
     */
    #[Route('/me', name: 'app_agenda_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function myAgenda(): Response
    {
        $user = $this->getUser();
        // ✅ Récupérer les occurrences de cet utilisateur
        $arr_agenda = $this->agendaGenerator->getAgendaForUser($user);

        return $this->render('agenda/my_agenda.html.twig', [
            'agenda' => json_encode($arr_agenda),
            'agendaArray' => $arr_agenda,
        ]);
    }

    /**
     * Créer un nouvel RDV (Admin seulement)
     */
    #[Route('/create', name: 'app_agenda_create', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        EleveRepository $eleveRepository,
        ClasseRepository $classeRepository,
        AdminRepository $adminRepository
    ): Response {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données de la requête
            $dateDebut = $request->request->get('dateDebut');
            $heureDebut = $request->request->get('heureDebut');
            $recurrence = $request->request->get('recurrence', 'aucune');
            $recurrenceEnd = $request->request->get('recurrenceEnd');

            if ($dateDebut && $heureDebut) {
                $datetimeDebut = new \DateTime($dateDebut . ' ' . $heureDebut . ':00');
                $evenement->setHeureDebut($datetimeDebut);
            } else {
                $evenement->setHeureDebut(new \DateTime());
            }

            // Définir la récurrence
            $evenement->setRecurrence($recurrence);
            if ($recurrenceEnd) {
                $evenement->setRecurrenceEnd(new \DateTime($recurrenceEnd));
            }

            // Associer l'admin créateur
            $currentUser = $this->getUser();
            if ($currentUser) {
                $admin = $adminRepository->findOneBy(['user' => $currentUser]);
                if ($admin) {
                    $evenement->setAdminCreateur($admin);
                }
            }

            $evenement->setCreatedAt(new \DateTime());

            // Ajouter les élèves/utilisateurs
            $classeId = $request->request->get('classe');
            $eleveId = $request->request->get('eleve');

            if ($classeId && $classeId !== "-1") {
                $eleves = $eleveRepository->findEleveByClasse($classeId);
                foreach ($eleves as $eleve) {
                    if ($eleve->isValidated() && $eleve->getUser()) {
                        $evenement->addUser($eleve->getUser());
                    }
                }
            } elseif ($eleveId && $eleveId !== "-1") {
                $eleve = $eleveRepository->find($eleveId);
                if ($eleve && $eleve->isValidated() && $eleve->getUser()) {
                    $evenement->addUser($eleve->getUser());
                }
            }

            $entityManager->persist($evenement);
            $entityManager->flush();

            // ✅ Si c'est un événement récurrent, créer les occurrences
            if ($evenement->isParent()) {
                $this->agendaGenerator->createRecurringEvent($evenement);
            }

            $this->addFlash('success', 'RDV créé avec succès!');
            return $this->redirectToRoute('app_agenda');
        }

        return $this->render('agenda/form.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
            'eleves' => $eleveRepository->findBy(['isValidated' => true]),
            'classes' => $classeRepository->findAll(),
        ]);
    }

    /**
     * Éditer un RDV existant (Admin seulement)
     * ✅ Gère le parent ET les occurrences
     */
    #[Route('/{id}/edit', name: 'app_agenda_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function edit(
        Request $request,
        Evenement $evenement,
        EntityManagerInterface $entityManager,
        EleveRepository $eleveRepository,
        ClasseRepository $classeRepository
    ): Response {
        // Vérifier que l'admin a créé ce RDV
        $currentUser = $this->getUser();
        
        $parentEvent = $evenement->isOccurrence() ? $evenement->getParent() : $evenement;
        if ($parentEvent && $parentEvent->getAdminCreateur() && $parentEvent->getAdminCreateur()->getUser() !== $currentUser) {
            throw $this->createAccessDeniedException('Vous ne pouvez éditer que vos propres RDV');
        }

        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ Si c'est une occurrence, mettre à jour seulement celle-ci
            if ($evenement->isOccurrence()) {
                $data = [
                    'sujet' => $evenement->getSujet(),
                    'heureDebut' => $evenement->getHeureDebut()->format('Y-m-d H:i:s'),
                    'corps' => $evenement->getCorps(),
                    'lieu' => $evenement->getLieu(),
                    'duree' => $evenement->getDuree(),
                ];
                $this->agendaGenerator->updateOccurrence($evenement, $data);
            } 
            // ✅ Si c'est un parent, régénérer toutes les occurrences
            else if ($evenement->isParent()) {
                $this->agendaGenerator->updateRecurringEvent($evenement);
            } 
            // ✅ Sinon, c'est un simple événement
            else {
                $entityManager->persist($evenement);
                $entityManager->flush();
            }

            $this->addFlash('success', 'RDV mis à jour avec succès!');
            return $this->redirectToRoute('app_agenda');
        }

        return $this->render('agenda/form.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
            'isOccurrence' => $evenement->isOccurrence(),
            'eleves' => $eleveRepository->findBy(['isValidated' => true]),
            'classes' => $classeRepository->findAll(),
        ]);
    }

    /**
     * Voir les détails d'un RDV
     * ✅ Affiche le parent + toutes ses occurrences si c'est un récurrent
     */
    #[Route('/{id}', name: 'app_agenda_show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(
        Evenement $evenement,
        EleveRepository $eleveRepository,
        ClasseRepository $classeRepository,
        EvenementRepository $evenementRepository
    ): Response {
        // ✅ Si c'est une occurrence, afficher le parent + ses occurrences
        $displayEvent = $evenement->isOccurrence() ? $evenement->getParent() : $evenement;

        // Récupérer les élèves de l'événement groupés par classe
        $eleves_par_classe = [];
        $tous_les_eleves = [];

        foreach ($displayEvent->getUsers() as $user) {
            $eleve = $eleveRepository->findOneBy(['user' => $user]);
            if ($eleve) {
                $tous_les_eleves[] = $eleve;
                foreach ($eleve->getClasseEleves() as $classeEleve) {
                    $classe = $classeEleve->getClasse();
                    $classeId = $classe->getId();

                    if (!isset($eleves_par_classe[$classeId])) {
                        $eleves_par_classe[$classeId] = [
                            'classe' => $classe,
                            'eleves' => []
                        ];
                    }

                    if (!in_array($eleve, $eleves_par_classe[$classeId]['eleves'])) {
                        $eleves_par_classe[$classeId]['eleves'][] = $eleve;
                    }
                }
            }
        }

        // Récupérer toutes les classes
        $toutes_les_classes = $classeRepository->findAll();

        // ✅ Si c'est un parent, récupérer les occurrences
        $occurrences = [];
        if ($displayEvent->isParent()) {
            $occurrences = $displayEvent->getOccurrences()->toArray();
        }

        return $this->render('agenda/show.html.twig', [
            'evenement' => $displayEvent,
            'occurrences' => $occurrences,
            'eleves_par_classe' => $eleves_par_classe,
            'tous_les_eleves' => $tous_les_eleves,
            'toutes_les_classes' => $toutes_les_classes,
            'classes' => $toutes_les_classes,
            'isParent' => $displayEvent->isParent(),
        ]);
    }

    /**
     * Supprimer un RDV (Admin seulement)
     * ✅ Gère suppression du parent OU d'une occurrence
     */
    #[Route('/{id}/delete', name: 'app_agenda_delete', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(
        Request $request,
        Evenement $evenement,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérifier CSRF
        if (!$this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        // Vérifier que l'admin a créé ce RDV
        $currentUser = $this->getUser();
        
        $parentEvent = $evenement->isOccurrence() ? $evenement->getParent() : $evenement;
        if ($parentEvent && $parentEvent->getAdminCreateur() && $parentEvent->getAdminCreateur()->getUser() !== $currentUser) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres RDV');
        }

        // ✅ Si c'est une occurrence, supprimer juste celle-ci
        if ($evenement->isOccurrence()) {
            $this->agendaGenerator->deleteOccurrence($evenement);
            $this->addFlash('success', 'Occurrence supprimée avec succès!');
        }
        // ✅ Si c'est un parent, supprimer le parent (les enfants seront supprimés en cascade)
        else if ($evenement->isParent()) {
            $entityManager->remove($evenement);
            $entityManager->flush();
            $this->addFlash('success', 'RDV récurrent et toutes ses occurrences supprimés!');
        }
        // ✅ Sinon, c'est un simple événement
        else {
            $entityManager->remove($evenement);
            $entityManager->flush();
            $this->addFlash('success', 'RDV supprimé avec succès!');
        }

        return $this->redirectToRoute('app_agenda');
    }

    /**
     * API: Créer un RDV via AJAX (Admin seulement)
     * ✅ Support du système parent-child
     */
    #[Route('/api/create', name: 'app_agenda_api_create', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function apiCreate(
        Request $request,
        EntityManagerInterface $entityManager,
        EleveRepository $eleveRepository,
        AdminRepository $adminRepository
    ): JsonResponse {
        $response = new JsonResponse();

        // Vérifier CSRF
        $csrfToken = $request->request->get('create-time');
        if (!$this->isCsrfTokenValid('create-time', $csrfToken)) {
            $response->setData(['error' => 'Invalid CSRF token']);
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }

        try {
            $str_d_debut = $request->request->get('dateDebut');
            $str_h_debut = $request->request->get('heureDebut');
            $str_d_fin = $request->request->get('dateFin');
            $str_h_fin = $request->request->get('heureFin');

            $dt_debut = new \DateTime($str_d_debut . ' ' . $str_h_debut . ':00');
            $dt_fin = new \DateTime($str_d_fin . ' ' . $str_h_fin . ':00');

            $duree = $dt_fin->getTimestamp() - $dt_debut->getTimestamp();

            $str_objet = $request->request->get('data');
            $str_idEleve = $request->request->get('eleve');
            $str_idClasse = $request->request->get('classe');
            $str_lieu = $request->request->get('lieu');
            $str_sujet = $request->request->get('sujet');
            $str_recurrence = $request->request->get('recurrence', 'aucune');
            $recurrenceEnd = $request->request->get('recurrenceEnd');

            // Récupérer les élèves
            if ($str_idClasse !== "-1") {
                $arr_eleve = $eleveRepository->findEleveByClasse($str_idClasse);
            } elseif ($str_idEleve !== "-1") {
                $arr_eleve = [$eleveRepository->find($str_idEleve)];
            } else {
                $arr_eleve = [];
            }

            if (count($arr_eleve) > 0) {
                // ✅ Créer l'événement parent (parent-child pattern)
                $obj_evenement = new Evenement();
                $obj_evenement->setSujet($str_sujet);
                $obj_evenement->setCorps($str_objet);
                $obj_evenement->setLieu($str_lieu);
                $obj_evenement->setDuree($duree);
                $obj_evenement->setHeureDebut($dt_debut);
                $obj_evenement->setRecurrence($str_recurrence);
                
                if ($recurrenceEnd) {
                    $obj_evenement->setRecurrenceEnd(new \DateTime($recurrenceEnd));
                }

                $obj_evenement->setCreatedAt(new \DateTime());

                // Associer l'admin
                $currentUser = $this->getUser();
                if ($currentUser) {
                    $admin = $adminRepository->findOneBy(['user' => $currentUser]);
                    if ($admin) {
                        $obj_evenement->setAdminCreateur($admin);
                    }
                }

                // Ajouter les élèves
                foreach ($arr_eleve as $eleve) {
                    if ($eleve && $eleve->isValidated() && $eleve->getUser()) {
                        $obj_evenement->addUser($eleve->getUser());
                    }
                }

                $entityManager->persist($obj_evenement);
                $entityManager->flush();

                // ✅ Si c'est un événement récurrent, créer les occurrences
                if ($obj_evenement->isParent()) {
                    $this->agendaGenerator->createRecurringEvent($obj_evenement);
                }

                $response->setData(['ok' => 'RDV created successfully', 'id' => $obj_evenement->getId()]);
            } else {
                $response->setData(['error' => 'No valid students found']);
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            }

        } catch (\Exception $e) {
            $response->setData(['error' => $e->getMessage()]);
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    /**
     * Lister les RDV à venir (JSON)
     * ✅ Retourne les occurrences uniquement au format FullCalendar
     */
    #[Route('/api/upcoming', name: 'app_agenda_api_upcoming', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function apiUpcoming(): JsonResponse
    {
        $events = $this->agendaGenerator->getUpcomingEvents();
        
        // Format FullCalendar : les données sont déjà correctement formatées par le service
        return new JsonResponse($events);
    }

    /**
     * Filtrer l'agenda par date spécifique
     * ✅ Adapté pour le système parent-child et format FullCalendar
     */
    private function filterAgendaByDate(array $agenda, \DateTime $targetDate): array
    {
        $targetDateStr = $targetDate->format('Y-m-d');

        return array_filter($agenda, function ($event) use ($targetDateStr) {
            // Format FullCalendar: 'start' contient la chaîne ISO datetime
            $eventDate = isset($event['start']) ? substr($event['start'], 0, 10) : null;
            return $eventDate === $targetDateStr;
        });
    }
}
