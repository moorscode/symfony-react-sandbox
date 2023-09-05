<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Recipe controller.
 */
class RecipeController extends AbstractController
{
    private RecipeRepository $recipeRepository;

    /**
     * @param RecipeRepository $recipeRepository
     */
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
    public function homeAction(SerializerInterface $serializer): Response
    {
        $recipes = $this->recipeRepository->findAll();

        return $this->render('recipe/home.html.twig', [
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
    public function recipeAction(string $id, SerializerInterface $serializer): Response
    {
        $recipe = $this->recipeRepository->find($id);
        if (!$recipe) {
            throw $this->createNotFoundException('The recipe does not exist');
        }

        return $this->render('recipe/recipe.html.twig', [
            'props' => $serializer->normalize(['recipe' => $recipe]),
        ]);
    }

    /**
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    #[Route("/redux/", name: "homepage_redux")]
    public function homeReduxAction(SerializerInterface $serializer): Response
    {
        $recipes = $this->recipeRepository->findAll();

        return $this->render('recipe-redux/home.html.twig', [
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
    public function recipeReduxAction(string $id, SerializerInterface $serializer): Response
    {
        $recipe = $this->recipeRepository->find($id);
        if (!$recipe) {
            throw $this->createNotFoundException('The recipe does not exist');
        }

        return $this->render('recipe-redux/recipe.html.twig', [
            'initialState' => $serializer->normalize(['recipe' => $recipe]),
        ]);
    }

}
