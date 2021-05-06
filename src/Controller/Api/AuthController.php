<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\Auth\RegisterType;
use App\Traits\AuthServiceAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/auth", name="api_auth_")
 */
class AuthController extends BaseController
{
    use AuthServiceAwareTrait;

    /**
     * @Route("/register", name="register", methods={"POST"})
     * @OA\Post(
     *     tags={"Auth"},
     *     summary="Register user",
     *     description="Register and login new user in the system",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref=@Model(type=RegisterType::class))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User successfully created and logged in",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation failed",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="code", type="integer", example=400),
     *                 @OA\Property(property="message", type="string", example="Validation Failed"),
     *                 @OA\Property(
     *                     property="errors",
     *                     type="object",
     *                     @OA\Property(
     *                         property="children",
     *                         type="object",
     *                         @OA\Property(
     *                             property="email",
     *                             type="array",
     *                             @OA\Items(example="This email is used by another user")
     *                         ),
     *                         @OA\Property(
     *                             property="password",
     *                             type="array",
     *                             @OA\Items(example="This value is required")
     *                         ),
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function registerAction(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->badRequestResponse($form);
        }

        /** @var User $user */
        $user = $form->getData();
        $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute(
            'api_auth_login',
            [
                'email' => $form->get('email')->getData(),
                'password' => $form->get('password')->getData()
            ],
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
