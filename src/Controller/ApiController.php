<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * The API controller.
 */
class ApiController extends AbstractController
{
    /**
     * @var RecipeRepository
     */
    private RecipeRepository $recipeRepository;

    /**
     * @param RecipeRepository $recipeRepository
     */
    public function __construct(
        RecipeRepository $recipeRepository,
    ) {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * Needed for client-side navigation after initial page load
     *
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    #[Route("/api/recipes", name: "api_recipes")]
    public function apiRecipesAction(SerializerInterface $serializer): JsonResponse
    {
        $recipes = $this->recipeRepository->findAll();

        return new JsonResponse($serializer->normalize($recipes));
    }

    /**
     * Needed for client-side navigation after initial page load
     * @param string              $id
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    #[Route("/api/recipes/{id}", name: "api_recipe")]
    public function apiRecipeAction(string $id, SerializerInterface $serializer): JsonResponse
    {
        $recipe = $this->recipeRepository->find($id);

        return new JsonResponse($serializer->normalize($recipe));
    }

}
