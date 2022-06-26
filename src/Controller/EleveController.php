<?php

namespace App\Controller;

use App\Entity\Eleve;
use App\Form\EleveType;
use App\Entity\Niveau;
use App\Repository\EleveRepository;
use App\Repository\NiveauRepository;
use App\Repository\ClasseRepository;
use App\Repository\ClasseEleveRepository;
use App\Entity\ClasseEleve;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eleve")
 */
class EleveController extends AbstractController
{
    /**
     * @Route("/", name="app_eleve_index", methods={"GET"})
     */
    public function index(EleveRepository $eleveRepository): Response
    {
        return $this->render('eleve/index.html.twig', [
            'eleves' => $eleveRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_eleve_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EleveRepository $eleveRepository, NiveauRepository $niveauRepository, ClasseRepository $classeRepository, ClasseEleveRepository $classeEleveRepository): Response
    {
        $eleve = new Eleve();
        $niveaux = $niveauRepository->findAll();
        $form = $this->createForm(EleveType::class, $eleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $eleveRepository->add($eleve);
            $id_classe = $request->request->get('classe_disponible');
            if(!is_null($id_classe))
            {
                $classe = $classeRepository->findOneBy(array("id"=>$id_classe));
                $classeEleve = new ClasseEleve();
                $classeEleve->setClasse($classe);
                $classeEleve->setEleve($eleve);
                $classeEleveRepository->add($classeEleve);
            }
            return $this->redirectToRoute('app_eleve_index', [], Response::HTTP_SEE_OTHER);
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
}
