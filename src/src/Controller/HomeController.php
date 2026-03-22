<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\ParentEleve;
use App\Entity\Eleve;
use App\Entity\Admin;
use App\Form\UpdateParentEleveType;
use App\Form\UpdateEleveType;
use App\Form\UpdateAdminType;
use App\Form\ChangePasswordType;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class HomeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route("/", name:"app_home")]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route("/parametre", name:"app_parametre", methods: ['GET', 'POST'])]
    public function getParametre(Request $request): Response 
    {
        $user = $this->getUser();
        $entity = null;
        $formType = null;
        $updateForm = null;
        $passwordForm = null;
        $successMessage = null;
        $errorMessage = null;

        // Déterminer le type d'utilisateur et récupérer l'entité associée
        if ($user instanceof User) {
            // Vérifier si c'est un admin
            $admin = $this->entityManager->getRepository(Admin::class)->findOneBy(['user' => $user]);
            if ($admin) {
                $entity = $admin;
                $formType = UpdateAdminType::class;
            } else {
                // Vérifier si c'est un parent
                $parent = $this->entityManager->getRepository(ParentEleve::class)->findOneBy(['user' => $user]);
                if ($parent) {
                    $entity = $parent;
                    $formType = UpdateParentEleveType::class;
                } else {
                    // Vérifier si c'est un élève
                    $eleve = $this->entityManager->getRepository(Eleve::class)->findOneBy(['user' => $user]);
                    if ($eleve) {
                        $entity = $eleve;
                        $formType = UpdateEleveType::class;
                    }
                }
            }
        }

        if ($entity && $formType) {
            // Créer le formulaire de mise à jour des informations personnelles
            $updateForm = $this->createForm($formType, $entity);
            $updateForm->handleRequest($request);

            if ($updateForm->isSubmitted() && $updateForm->isValid()) {
                $this->entityManager->flush();
                $successMessage = 'Vos informations personnelles ont été mises à jour avec succès !';
            }
        }

        // Créer le formulaire de changement de mot de passe
        $passwordForm = $this->createForm(ChangePasswordType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $data = $passwordForm->getData();
            $currentPassword = $data['currentPassword'] ?? null;
            $newPassword = $data['newPassword'] ?? null;

            // Vérifier le mot de passe actuel
            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                $errorMessage = 'Le mot de passe actuel est incorrect.';
            } elseif ($newPassword === $currentPassword) {
                $errorMessage = 'Le nouveau mot de passe doit être différent de l\'ancien.';
            } else {
                // Mettre à jour le mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $this->entityManager->flush();
                $successMessage = 'Votre mot de passe a été changé avec succès !';
                $passwordForm = $this->createForm(ChangePasswordType::class); // Réinitialiser le formulaire
            }
        }

        $entity = match (true) {
            $entity instanceof Admin => 'Admin',
            $entity instanceof ParentEleve => 'Parent',
            $entity instanceof Eleve => 'Eleve',
            default => null,
        };

        return $this->render('home/parametre.html.twig', [
            'user' => $user,
            'entity' => $entity,
            'updateForm' => $updateForm,
            'passwordForm' => $passwordForm,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
            'userType' => $entity,
        ]);
    }
}
