<?php
namespace App\Controller;
use App\Entity\Niveau;
use App\Entity\Classe;
use App\Form\NiveauType;
use App\Repository\NiveauRepository;
use App\Repository\ClasseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

/**
 * @Route("/niveau")
 */
class NiveauController extends AbstractController
{
     /**
     * @Route("/apptoJson", name="app_niveau_json", methods={"POST"})
     */
    public function toJsonAction(Request $request, NiveauRepository $niveauRepository): JsonResponse
    {
        $id = $request->request->get('id');
        if(!is_null($id))
        {
        $niveau = $niveauRepository->findOneBy(["id"=>$id]);
            if(!is_null($niveau))
            {
                $classe = $niveau->getClasse();
                $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
                $normalizer = new ObjectNormalizer($classMetadataFactory); 
                $encoder = new JsonEncoder();  
                $serializer = new Serializer([$normalizer],[$encoder]);
                $jsonContent = $serializer->serialize($classe, 'json',['groups'=>'list_class']);
                return JsonResponse::fromJsonString($jsonContent);
            }
        }
        return new JsonResponse([]);

    }
    /**
     * @Route("/", name="app_niveau_index", methods={"GET"})
     */
    public function index(NiveauRepository $niveauRepository): Response
    {
        return $this->render('niveau/index.html.twig', [
            'niveaux' => $niveauRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_niveau_new", methods={"GET", "POST"})
     */
    public function new(Request $request, NiveauRepository $niveauRepository, ClasseRepository $classeRepository): Response
    {
        $niveau = new Niveau();
        $form = $this->createForm(NiveauType::class, $niveau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = 1;
            $key = "niveau_classe_".strval($item)."_nom";
            if($request->query->has($key))
            {
                while($request->query->has($key))
                {
                    $classe = new Classe();
                    $t_nomClasse = $request->get($key);
                    $classe->setNom($request->get($key));
                    $niveau->addClasse($classe);
                    $item = $item +1;
                }
            }
            $niveauRepository->add($niveau);

            return $this->redirectToRoute('app_niveau_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('niveau/new.html.twig', [
            'niveau' => $niveau,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_niveau_show", methods={"GET"})
     */
    public function show(Niveau $niveau): Response
    {
        return $this->render('niveau/show.html.twig', [
            'niveau' => $niveau,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_niveau_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Niveau $niveau, NiveauRepository $niveauRepository): Response
    {
        $form = $this->createForm(NiveauType::class, $niveau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $niveauRepository->add($niveau);
            return $this->redirectToRoute('app_niveau_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('niveau/edit.html.twig', [
            'niveau' => $niveau,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_niveau_delete", methods={"POST"})
     */
    public function delete(Request $request, Niveau $niveau, NiveauRepository $niveauRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$niveau->getId(), $request->request->get('_token'))) {
            $niveauRepository->remove($niveau);
        }

        return $this->redirectToRoute('app_niveau_index', [], Response::HTTP_SEE_OTHER);
    }



}
