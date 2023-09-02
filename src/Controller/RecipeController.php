<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
     * #[Route("/", name: "homepage")]
     *
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    public function homeAction(SerializerInterface $serializer)
    {
        $recipes = $this->recipeRepository->findAll();

        return $this->render('recipe/home.html.twig', [
            // We pass an array as props
            'props' => $serializer->normalize(['recipes' => $recipes]),
        ]);
    }

    /**
     * #[Route("/recipe/{id}", name: "recipe")]
     *
     * @param string              $id
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    public function recipeAction($id, SerializerInterface $serializer)
    {
        $recipe = $this->recipeRepository->find($id);
        if (!$recipe) {
            throw $this->createNotFoundException('The recipe does not exist');
        }

        return $this->render('recipe/recipe.html.twig', [
            // A JSON string also works
            'props' => $serializer->serialize(
                ['recipe' => $recipe],
                'json'
            ),
        ]);
    }

    /**
     * #[Route("/redux/", name: "homepage_redux")]
     *
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    public function homeReduxAction(SerializerInterface $serializer)
    {
        $recipes = $this->recipeRepository->findAll();

        return $this->render('recipe-redux/home.html.twig', [
            // We pass an array as props
            'initialState' => $serializer->normalize(
                ['recipes' => $recipes]
            ),
        ]);
    }

    /**
     * #[Route("/redux/recipe/{id}", name: "recipe_redux")]
     *
     * @param string              $id
     * @param SerializerInterface $serializer
     *
     * @return Response
     */
    public function recipeReduxAction($id, SerializerInterface $serializer)
    {
        $recipe = $this->recipeRepository->find($id);
        if (!$recipe) {
            throw $this->createNotFoundException('The recipe does not exist');
        }

        return $this->render('recipe-redux/recipe.html.twig', [
            // A JSON string also works
            'initialState' => $serializer->serialize(
                [
                    'recipe' => $recipe,
                ],
                'json'
            ),
        ]);
    }

}
