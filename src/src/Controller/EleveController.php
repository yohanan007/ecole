<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Eleve;
use App\Entity\User;
use App\Form\EleveType;
use App\Entity\Niveau;
use App\Entity\ParentEleve;
use App\Repository\EleveRepository;
use App\Repository\UserRepository;
use App\Repository\NiveauRepository;
use App\Repository\ClasseRepository;
use App\Repository\ClasseEleveRepository;
use App\Repository\ParentEleveRepository;
use App\Entity\ClasseEleve;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/eleve")
 */
class EleveController extends AbstractController
{
    /**
     * @Route("/", name="app_eleve_index", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function index(EleveRepository $eleveRepository): Response
    {
        //utilisation de ternaire basique
        /*return ($this->IsGranted("ROLE_ADMIN") ?
         $this->render('eleve/index.html.twig', [
            'eleves' => $eleveRepository->findAll(),
        ]) : $this->isGranted("ROLE_USER") ? $this->render('eleve/index.html.twig', [
            'eleves' => $eleveRepository->findAll(),"autre"=>"autre"]): $this->render('eleve/index.html.twig', [
            'eleves' => $eleveRepository->findAll(),"autre"=>"truc"]));*/
        switch(true){
            case $this->IsGranted("ROLE_ADMIN"):
            return $this->redirectToRoute('app_parent_eleve_index', []);
            break;
            case $this->IsGranted("ROLE_USER"):
            return $this->redirectToRoute('app_parent_eleve_index', []);
            break;
            default:
            return $this->redirectToRoute('app_home', []);
            break;
        }
    }

    /**
     * @Route("/new", name="app_eleve_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_PARENT")
     */
    public function new(Request $request,ManagerRegistry $doctrine, EleveRepository $eleveRepository, NiveauRepository $niveauRepository, UserRepository $userRepository, ClasseEleveRepository $classeEleveRepository, ClasseRepository $classeRepository,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $eleve = new Eleve();
        $user = new User();
        $niveaux = $niveauRepository->findAll();
        $form = $this->createForm(EleveType::class, $eleve);
        $form_User = $form->get('user');
        $form_User->remove('password')->remove('isVerified')->remove('roles');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $t_email = $request->request->get('eleve')['user']['email'];
            $user->setEmail($t_email);
            $t_mdp =$this->genereMdp();
            $user->setPassword($userPasswordHasher->hashPassword(
                    $user,
                    $t_mdp
                ));
            $userRepository->add($user);
            $eleve->setUser($user);
            $eleveRepository->add($eleve);
            $id_classe = $request->request->get('classe_disponible');
           
            
            if(!is_null($id_classe))
            {
                $classe = $classeRepository->findOneBy(array("id"=>$id_classe));
                $classeEleve = new ClasseEleve();
                $classeEleve->setClasse($classe);
                $classeEleve->setEleve($eleve);
                $classeEleveRepository->add($classeEleve);
                
                if($request->request->has("parent_id"))
                {
                    $int_id = $request->request->get("parent_id");
                    $entityManager = $doctrine->getManager();
                    $user_parent = $entityManager->getRepository(ParentEleve::class)->findOneBy(array("user"=>$int_id));
                    
                    if(!is_null($user_parent))
                    {
                        $user_parent->addEnfant($eleve);
                        $entityManager->persist($user_parent);
                        $entityManager->flush();
                    }
                }

            }
            return $this->redirectToRoute('app_parent_eleve_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('eleve/new.html.twig', [
            'eleve' => $eleve,
            'niveaux' => $niveaux,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_eleve_show", methods={"GET"})
     */
    public function show(Eleve $eleve): Response
    {
        return $this->render('eleve/show.html.twig', [
            'eleve' => $eleve,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_eleve_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Eleve $eleve, EleveRepository $eleveRepository): Response
    {
        $form = $this->createForm(EleveType::class, $eleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eleveRepository->add($eleve);
            return $this->redirectToRoute('app_eleve_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('eleve/edit.html.twig', [
            'eleve' => $eleve,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_eleve_delete", methods={"POST"})
     */
    public function delete(Request $request, Eleve $eleve, EleveRepository $eleveRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$eleve->getId(), $request->request->get('_token'))) {
            $eleveRepository->remove($eleve);
        }

        return $this->redirectToRoute('app_eleve_index', [], Response::HTTP_SEE_OTHER);
    }

    //genere un mot de passe aléatoire 
    public function genereMdp()
    {
        $arr_alphabet = [];
        //genere l'alphabet
        foreach(range('A','Z') as $i) 
        {
            $i_indicMin =  rand(1,2);
            if(($i_indicMin%2)==0)
            {
                $arr_alphabet[] = $i;
            }
            else
            {
                $arr_alphabet[] = strtolower($i);
            }
            
        }

        $arr_chiffre = [];
        //genere les chiffres
        foreach(range(1,9) as $j)
        {
            $arr_chiffre[]=strval($j);
        }

        //caractere seciaux accepté
        $arr_caractere = ['@','#','+','(',')','*','-','|','/','>','<'];

        $i_nbCaractere = 12;
        $i_nbLettre = rand(6,8);
        $i_nbChiffre = $i_nbCaractere - $i_nbLettre - 1;
        $i_nbSpecial = 1;

        $arr_mdp = [];
        for ($i = 1; $i <= $i_nbCaractere; $i++) 
            {
                $pos = rand(0,($i_nbLettre-1));
                $arr_mdp[] = $arr_alphabet[$pos];
            }

        $l=0;

        // on intégre les chiffres
        $arr_posChiffre = [];

        while($l < ($i_nbChiffre+1))
        {
            $poschiffre = rand(0,($i_nbChiffre-1));
            $t_chiffre = $arr_chiffre[$poschiffre];
            $posmdp = rand(0,($i_nbCaractere-1));
            if(!in_array($posmdp,$arr_posChiffre))
            {
                $arr_posChiffre[] = $posmdp;
                $l++;
                $arr_mdp[$posmdp] = $t_chiffre;
            }

        }

        //derniere étape on intégre un caractére spécial
        $b_ok = false;
        while(!$b_ok)
        {
            $i_posCaractere = rand(0,(count($arr_caractere)-1));
            $i_posMdp =  rand(0,($i_nbCaractere-1));
            if(!in_array($i_posMdp,$arr_posChiffre))
            {
                 $arr_mdp[$i_posMdp] = $arr_caractere[$i_posCaractere];
                 $b_ok = true;
            }
        }

        $t_mdp =  "";
        for($k=0;$k<count($arr_mdp);$k++)
        {
            $t_mdp = $t_mdp.$arr_mdp[$k];
        }

        return $t_mdp;

    }
}
