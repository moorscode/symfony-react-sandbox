<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends AbstractController
{
    /**
     * @var RecipeRepository
     */
    private $recipeRepository;

    public function __construct(
        RecipeRepository $recipeRepository,
    ) {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * #[Route("/api/recipes", name:"api_recipes")]
     *
     * Needed for client-side navigation after initial page load
     */
    public function apiRecipesAction(SerializerInterface $serializer)
    {
        $recipes = $this->recipeRepository->findAll();

        return new JsonResponse($serializer->normalize($recipes));
    }

    /**
     * #[Route("/api/recipes/{id}", name:"api_recipe")]
     *
     * Needed for client-side navigation after initial page load
     */
    public function apiRecipeAction($id, SerializerInterface $serializer)
    {
        $recipe = $this->recipeRepository->find($id);

        return new JsonResponse($serializer->normalize($recipe));
    }

}
