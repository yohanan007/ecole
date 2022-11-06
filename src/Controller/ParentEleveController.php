<?php

namespace App\Controller;

use App\Service\UserGenerator;
use App\Service\ParentGenerator;
use App\Entity\User;
use App\Entity\ParentEleve;
use App\Form\ParentEleveType;
use App\Repository\ParentEleveRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/parent/eleve")
 */
class ParentEleveController extends AbstractController
{
    /**
     * @Route("/", name="app_parent_eleve_index", methods={"GET"})
     * 
     */
    public function index(UserGenerator $userGenerator, ParentGenerator $parentGenerator, ParentEleveRepository $parentEleveRepository): Response
    {
        $b_isAdmin = $userGenerator->isAdmin();
        if($b_isAdmin)
        {
                return $this->render('parent_eleve/index.html.twig', [
                'parent_eleves' => $parentEleveRepository->findAll(),
            ]);
        }
        else
        {
            //dump($parentGenerator->getEleveParent());
                return $this->render('parent_eleve/index.html.twig', [
                'parent_eleves' => $parentGenerator->getEleveParent(),
            ]);        
        }

    }

    /**
     * @Route("/new", name="app_parent_eleve_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ParentEleveRepository $parentEleveRepository,UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $obj_entParentEleve = new ParentEleve();
    
        $form = $this->createForm(ParentEleveType::class, $obj_entParentEleve);
        //gestion du sous formulaire user 
        $userForm = $form->get('user');
        $userForm->remove('roles')->remove('isVerified');
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $t_email = $request->request->get('parent_eleve')['user']['email'];
            $t_password = $request->request->get('parent_eleve')['user']['password']['first'];
            $t_passwordRepeat = $request->request->get('parent_eleve')['user']['password']['second'];
            if((filter_var($t_email, FILTER_VALIDATE_EMAIL))&&($t_password !== "")&&($t_passwordRepeat === $t_password))
            {
                $obj_entUser = new User();
                $obj_entUser->setIsVerified(false);
                $obj_entUser->setEmail($t_email);
                $obj_entUser->setPassword($userPasswordHasher->hashPassword(
                    $obj_entUser,
                    $t_password
                ));
                $obj_entUser->setRoles(["ROLE_USER"]);
                $userRepository->add($obj_entUser);
                $obj_entParentEleve->setUser($obj_entUser);
                $parentEleveRepository->add($obj_entParentEleve);
                return $this->redirectToRoute('app_parent_eleve_index', [], Response::HTTP_SEE_OTHER);
            }

        }

        return $this->renderForm('parent_eleve/new.html.twig', [
            'parent_eleve' => $obj_entParentEleve,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_parent_eleve_show", methods={"GET"})
     */
    public function show(ParentEleve $parentEleve): Response
    {
        return $this->render('parent_eleve/show.html.twig', [
            'parent_eleve' => $parentEleve,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_parent_eleve_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, ParentEleve $parentEleve, ParentEleveRepository $parentEleveRepository): Response
    {
        $form = $this->createForm(ParentEleveType::class, $parentEleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parentEleveRepository->add($parentEleve);
            return $this->redirectToRoute('app_parent_eleve_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('parent_eleve/edit.html.twig', [
            'parent_eleve' => $parentEleve,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_parent_eleve_delete", methods={"POST"})
     */
    public function delete(Request $request, ParentEleve $parentEleve, ParentEleveRepository $parentEleveRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$parentEleve->getId(), $request->request->get('_token'))) {
            $parentEleveRepository->remove($parentEleve);
        }

        return $this->redirectToRoute('app_parent_eleve_index', [], Response::HTTP_SEE_OTHER);
    }
}
