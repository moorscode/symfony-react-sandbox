<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RecipeController extends AbstractController
{
    /**
     * @var RecipeRepository
     */
    private $recipeRepository;

    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    #[Route("/", name: "homepage")]
    public function homeAction(SerializerInterface $serializer)
    {
        $recipes = $this->recipeRepository->findAll();

        return $this->render('recipe/home.html.twig', [
            // We pass an array as props
            'props' => $serializer->normalize(['recipes' => $recipes]),
        ]);
    }

    /**
     * @param string              $id
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    #[Route("/recipe/{id}", name: "recipe")]
    public function recipeAction($id, SerializerInterface $serializer)
    {
        $recipe = $this->recipeRepository->find($id);
        if (!$recipe) {
            throw $this->createNotFoundException('The recipe does not exist');
        }

        return $this->render('recipe/recipe.html.twig', [
            // A JSON string also works
            'props' => $serializer->normalize(['recipe' => $recipe]),
        ]);
    }

    /**
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    #[Route("/redux/", name: "homepage_redux")]
    public function homeReduxAction(SerializerInterface $serializer)
    {
        $recipes = $this->recipeRepository->findAll();

        return $this->render('recipe-redux/home.html.twig', [
            // We pass an array as props
            'initialState' => $serializer->normalize(['recipes' => $recipes]),
        ]);
    }

    /**
     * @param string              $id
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    #[Route("/redux/recipe/{id}", name: "recipe_redux")]
    public function recipeReduxAction($id, SerializerInterface $serializer)
    {
        $recipe = $this->recipeRepository->find($id);
        if (!$recipe) {
            throw $this->createNotFoundException('The recipe does not exist');
        }

        return $this->render('recipe-redux/recipe.html.twig', [
            // A JSON string also works
            'initialState' => $serializer->normalize(['recipe' => $recipe]),
        ]);
    }

}
