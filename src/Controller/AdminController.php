<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\Type\RecipeType;
use App\Repository\RecipeRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\CookieTokenExtractor;
use Limenius\Liform\Liform;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * The admin controller.
 */
class AdminController extends AbstractController
{
    /**
     * @var RecipeRepository
     */
    private RecipeRepository $recipeRepository;

    /**
     * @param RecipeRepository $recipeRepository
     */
    public function __construct(
        RecipeRepository $recipeRepository
    ) {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * @param Liform              $liform
     * @param SerializerInterface $serializer
     * @param Request             $request
     *
     * @return Response
     */
    #[Route("/admin/liform/", name: "liform")]
    public function liformAction(Liform $liform, SerializerInterface $serializer, Request $request): Response
    {
        try {
            $token = $this->getValidToken($request);
            $recipe = new Recipe();
            $form = $this->createForm(
                RecipeType::class,
                $recipe,
                array('csrf_protection' => false)
            );

            $recipes = $this->recipeRepository->findAll();

            return $this->render('admin/index.html.twig', [
                'authToken' => $token,
                'recipes' => $serializer->normalize($recipes),
                'schema' => $liform->transform($form),
                'initialValues' => $serializer->normalize($form->createView()),
            ]);
        } catch (\Exception $e) {
            return $this->render('admin/index.html.twig', [
                'authToken' => null,
                'schema' => null,
                'recipes' => [],
                'initialValues' => null,
                'props' => [],
            ]);
        }
    }

    /**
     * @param Liform              $liform
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    #[Route("/admin/api/form", name: "admin_form", condition: "context.getMethod() in ['GET']")]
    public function getFormAction(Liform $liform, SerializerInterface $serializer): JsonResponse
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::Class, $recipe);

        return new JsonResponse([
            'schema' => $liform->transform($form),
            'initialValues' => $serializer->normalize($form->createView()),
        ]);
    }

    /**
     * @param Request             $request
     * @param SerializerInterface $serializer
     *
     * @return JsonResponse|Response
     */
    #[Route("/admin/api/recipes", name: "liform_post", condition: "context.getMethod() in ['POST']")]
    public function liformPostAction(Request $request, SerializerInterface $serializer)
    {
        $recipe = new Recipe();
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(RecipeType::Class, $recipe);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->recipeRepository->save($recipe);

            $response = new Response($serializer->serialize($recipe, 'json'), 201);
            $response->headers->set(
                'Location',
                'We should provide a url here, but this is a dummy example and there is no location where you can retrieve a single recipe, so...'
            );
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        return new JsonResponse($serializer->normalize($form), 400);
    }

    /**
     * @param Request $request
     *
     * @return PreAuthenticationJWTUserToken|void
     */
    private function getValidToken(Request $request)
    {
        $tokenExtractor = new CookieTokenExtractor('BEARER');

        if (false === ($jsonWebToken = $tokenExtractor->extract($request))) {
            return;
        }

        $preAuthToken = new PreAuthenticationJWTUserToken($jsonWebToken);
        try {
            if (!$payload = $this->get('lexik_jwt_authentication.jwt_manager')->decode($preAuthToken)) {
                throw new InvalidTokenException('Invalid JWT Token');
            }
            $preAuthToken->setPayload($payload);
        } catch (JWTDecodeFailureException $e) {
            if (JWTDecodeFailureException::EXPIRED_TOKEN === $e->getReason()) {
                throw new ExpiredTokenException();
            }
            throw new InvalidTokenException('Invalid JWT Token', 0, $e);
        }

        return $preAuthToken;
    }
}
