<?php

namespace App\Controller;

use App\Entity\ParentEleve;
use App\Form\ParentEleveType;
use App\Repository\ParentEleveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/parent/eleve")
 */
class ParentEleveController extends AbstractController
{
    /**
     * @Route("/", name="app_parent_eleve_index", methods={"GET"})
     */
    public function index(ParentEleveRepository $parentEleveRepository): Response
    {
        return $this->render('parent_eleve/index.html.twig', [
            'parent_eleves' => $parentEleveRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_parent_eleve_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ParentEleveRepository $parentEleveRepository): Response
    {
        $parentEleve = new ParentEleve();
        $form = $this->createForm(ParentEleveType::class, $parentEleve);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parentEleveRepository->add($parentEleve);
            return $this->redirectToRoute('app_parent_eleve_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('parent_eleve/new.html.twig', [
            'parent_eleve' => $parentEleve,
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
