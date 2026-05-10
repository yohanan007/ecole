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
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\ClasseEleve;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;



#[Route("/eleve")]
class EleveController extends AbstractController
{
    
     #[Route("/", name:"app_eleve_index", methods:["GET"])]
     #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(EleveRepository $eleveRepository): Response
    {
        switch(true){
            case $this->IsGranted("ROLE_ADMIN"):
            return $this->redirectToRoute('app_eleve_list_admin', []);
            break;
            case $this->IsGranted("ROLE_PARENT"):
            return $this->redirectToRoute('app_parent_eleve_index', []);
            break;
            default:
            return $this->redirectToRoute('app_home', []);
            break;
        }
    }

    #[Route("/list", name:"app_eleve_list_admin", methods:["GET"])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminEleve(EleveRepository $eleveRepository): Response
    {
        return $this->render('eleve/index.html.twig', [
            'eleves' => $eleveRepository->findAll(),
         ]);
    }


     #[Route("/new", name:"app_eleve_new", methods:["GET", "POST"])]
     #[IsGranted("ROLE_PARENT")]
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
            $t_email = $request->request->all('eleve')['user']['email'];
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

        return $this->render('eleve/new.html.twig', [
            'eleve' => $eleve,
            'niveaux' => $niveaux,
            'form' => $form,
        ]);
    }

    #[Route("/list", name:"app_eleve_list", methods:["GET"])]
    public function listEleve(Request $obj_request,ClasseRepository $obj_classeRepository,EleveRepository $obj_eleveRepository, SerializerInterface $obj_serializer) : Response
    {
        if($this->isCsrfTokenValid('info_data_eleve',$obj_request->query->get('info_data_eleve')))
        {
            $initialContext = [
                'custom_key' => 'custom_value',
            ];

            $ob_context = (new ObjectNormalizerContextBuilder());


            $arr_reponse = [];

            if($obj_request->query->get('classe')){
            //todo : à modifié selon droit utilisateur et demande
            //voir pour passage à api

                $context  = $ob_context->withGroups('list_eleve')
                ->withContext($initialContext)
                ->toArray();

                $arr_reponse["data"] = $obj_eleveRepository->findEleveByClasse($obj_request->query->get('classe'));
            }else{

                $context = $ob_context->withGroups('list_class')
                ->withContext($initialContext)
                ->toArray();

                $arr_reponse["data"] = $obj_classeRepository->findAll();
            }
            $t_data_ = $obj_serializer->serialize($arr_reponse,'json',$context);
        }
        
        return JsonResponse::fromJsonString($t_data_);
    }

    #[Route("/{id}", name:"app_eleve_show", methods:["GET"])]
    public function show(Eleve $eleve): Response
    {
        return $this->render('eleve/show.html.twig', [
            'eleve' => $eleve,
        ]);
    }

    #[Route("/{id}/edit", name:"app_eleve_edit", methods:["GET", "POST"])]
    public function edit(Request $request, Eleve $eleve, EleveRepository $eleveRepository): Response
    {
        $form = $this->createForm(EleveType::class, $eleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eleveRepository->add($eleve);
            return $this->redirectToRoute('app_eleve_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('eleve/edit.html.twig', [
            'eleve' => $eleve,
            'form' => $form,
        ]);
    }

    #[Route("/{id}", name:"app_eleve_delete", methods:["POST"])]
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
