<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Eleve;
use App\Entity\Evenement;
use App\Entity\Agenda;
use App\Form\AdminType;
use App\Form\EleveValidationType;
use App\Repository\AdminRepository;
use App\Repository\EleveRepository;
use App\Repository\EvenementRepository;
use App\Repository\AgendaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

#[Route("/admin")]
class AdminController extends AbstractController
{
    /**
     * Dashboard d'administration
     */
    #[Route("/dashboard", name: "admin_dashboard", methods: ["GET"])]
    #[IsGranted("ROLE_ADMIN")]
    public function dashboard(
        AdminRepository $adminRepository,
        EleveRepository $eleveRepository,
        EvenementRepository $evenementRepository
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'total_admins' => count($adminRepository->findAll()),
            'total_eleves_en_attente' => count($eleveRepository->findBy(['isValidated' => false])),
            'total_eleves_valides' => count($eleveRepository->findBy(['isValidated' => true])),
            'total_rdv' => count($evenementRepository->findAll()),
        ]);
    }

    /**
     * Créer ou éditer un admin
     */
    #[Route("/create", name: "admin_create", methods: ["GET", "POST"])]
    #[Route("/{id}/edit", name: "admin_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_ADMIN")]
    public function createOrEdit(
        Request $request,
        EntityManagerInterface $entityManager,
        ?Admin $admin = null
    ): Response {
        if ($admin === null) {
            $admin = new Admin();
        }

        $form = $this->createForm(AdminType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // S'assurer que le User a le rôle ROLE_ADMIN
            if ($admin->getUser()) {
                $roles = $admin->getUser()->getRoles();
                if (!in_array('ROLE_ADMIN', $roles)) {
                    $roles[] = 'ROLE_ADMIN';
                    $admin->getUser()->setRoles($roles);
                }
            }

            $entityManager->persist($admin);
            $entityManager->flush();

            $this->addFlash('success', 'Admin enregistré avec succès!');
            return $this->redirectToRoute('admin_list');
        }

        return $this->render('admin/create.html.twig', [
            'form' => $form->createView(),
            'admin' => $admin,
            'title' => $admin->getId() ? 'Éditer l\'admin' : 'Créer un nouvel admin'
        ]);
    }

    /**
     * Lister tous les admins
     */
    #[Route("/list", name: "admin_list", methods: ["GET"])]
    #[IsGranted("ROLE_ADMIN")]
    public function list(AdminRepository $adminRepository): Response
    {
        return $this->render('admin/list.html.twig', [
            'admins' => $adminRepository->findAll(),
        ]);
    }

    /**
     * Voir les détails d'un admin
     */
    #[Route("/{id}", name: "admin_show", methods: ["GET"])]
    #[IsGranted("ROLE_ADMIN")]
    public function show(Admin $admin): Response
    {
        return $this->render('admin/show.html.twig', [
            'admin' => $admin,
        ]);
    }

    /**
     * Supprimer un admin
     */
    #[Route("/{id}/delete", name: "admin_delete", methods: ["POST"])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(
        Request $request,
        Admin $admin,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $admin->getId(), $request->request->get('_token'))) {
            // Optionnel: Mark as inactive instead of delete
            // $admin->setIsActive(false);
            // $entityManager->persist($admin);
            
            $entityManager->remove($admin);
            $entityManager->flush();
            $this->addFlash('success', 'Admin supprimé avec succès!');
        }

        return $this->redirectToRoute('admin_list');
    }

    /**
     * Lister et valider les élèves en attente
     */
    #[Route("/eleves/pending", name: "admin_eleves_pending", methods: ["GET"])]
    #[IsGranted("ROLE_ADMIN")]
    public function listPendingEleves(EleveRepository $eleveRepository): Response
    {
        $pendingEleves = $eleveRepository->findBy(['isValidated' => false]);

        return $this->render('admin/eleves_pending.html.twig', [
            'eleves' => $pendingEleves,
        ]);
    }

    /**
     * Valider un élève
     */
    #[Route("/eleves/{id}/validate", name: "admin_eleve_validate", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_ADMIN")]
    public function validateEleve(
        Request $request,
        Eleve $eleve,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(EleveValidationType::class, $eleve);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('validate')->isClicked()) {
                $eleve->setIsValidated(true);
                $eleve->setValidatedByAdmin($this->getUser());
                $eleve->setValidatedAt(new \DateTime());
                $entityManager->persist($eleve);
                $entityManager->flush();
                $this->addFlash('success', 'Élève validé avec succès!');
            } elseif ($form->get('reject')->isClicked()) {
                $eleve->setIsValidated(false);
                $eleve->setValidatedByAdmin(null);
                $eleve->setValidatedAt(null);
                $entityManager->persist($eleve);
                $entityManager->flush();
                $this->addFlash('warning', 'Inscription de l\'élève rejetée!');
            }

            return $this->redirectToRoute('admin_eleves_pending');
        }

        return $this->render('admin/eleve_validate.html.twig', [
            'eleve' => $eleve,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Lister tous les RDV (Evenement)
     */
    #[Route("/rdv/list", name: "admin_rdv_list", methods: ["GET"])]
    #[IsGranted("ROLE_ADMIN")]
    public function listRdv(EvenementRepository $evenementRepository): Response
    {
        return $this->render('admin/rdv_list.html.twig', [
            'evenements' => $evenementRepository->findAll(),
        ]);
    }

    /**
     * Créer un nouveau RDV
     */
    #[Route("/rdv/create", name: "admin_rdv_create", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_ADMIN")]
    public function createRdv(
        Request $request,
        EntityManagerInterface $entityManager,
        AgendaRepository $agendaRepository,
        AdminRepository $adminRepository
    ): Response {
        $evenement = new Evenement();
        
        // Créer une nouvelle Agenda si nécessaire
        $agenda = new Agenda();
        $agenda->setHeureDebut(new \DateTime());
        
        $form = $this->createFormBuilder($evenement)
            ->add('sujet')
            ->add('corps')
            ->add('lieu')
            ->add('duree')
            ->add('save', SubmitType::class, ['label' => 'Créer le RDV'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'admin connecté et l'associer au RDV
            $currentAdmin = $adminRepository->findOneBy(['user' => $this->getUser()]);
            if ($currentAdmin) {
                $evenement->setAdminCreateur($currentAdmin);
            }
            
            $evenement->setCreatedAt(new \DateTime());
            $evenement->setAgenda($agenda);

            $entityManager->persist($agenda);
            $entityManager->persist($evenement);
            $entityManager->flush();

            $this->addFlash('success', 'RDV créé avec succès!');
            return $this->redirectToRoute('admin_rdv_list');
        }

        return $this->render('admin/rdv_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Éditer un RDV
     */
    #[Route("/rdv/{id}/edit", name: "admin_rdv_edit", methods: ["GET", "POST"])]
    #[IsGranted("ROLE_ADMIN")]
    public function editRdv(
        Request $request,
        Evenement $evenement,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createFormBuilder($evenement)
            ->add('sujet')
            ->add('corps')
            ->add('lieu')
            ->add('duree')
            ->add('save', SubmitType::class, ['label' => 'Mettre à jour le RDV'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($evenement);
            $entityManager->flush();

            $this->addFlash('success', 'RDV mis à jour avec succès!');
            return $this->redirectToRoute('admin_rdv_list');
        }

        return $this->render('admin/rdv_edit.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
        ]);
    }

    /**
     * Supprimer un RDV
     */
    #[Route("/rdv/{id}/delete", name: "admin_rdv_delete", methods: ["POST"])]
    #[IsGranted("ROLE_ADMIN")]
    public function deleteRdv(
        Request $request,
        Evenement $evenement,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
            $this->addFlash('success', 'RDV supprimé avec succès!');
        }

        return $this->redirectToRoute('admin_rdv_list');
    }
}