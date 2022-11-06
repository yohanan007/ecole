<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/parametre", name="app_parametre")
     */
    public function getParametre(): Response 
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('home/parametre.html.twig', [
        'user' => $user,
        ]);

    }
}
