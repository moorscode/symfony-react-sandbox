<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
     * Needed for client-side navigation after initial page load
     */
    #[Route("/api/recipes", name:"api_recipes")]
    public function apiRecipesAction(SerializerInterface $serializer)
    {
        $recipes = $this->recipeRepository->findAll();

        return new JsonResponse($serializer->normalize($recipes));
    }

    /**
     * Needed for client-side navigation after initial page load
     */
    #[Route("/api/recipes/{id}", name:"api_recipe")]
    public function apiRecipeAction($id, SerializerInterface $serializer)
    {
        $recipe = $this->recipeRepository->find($id);

        return new JsonResponse($serializer->normalize($recipe));
    }

}
