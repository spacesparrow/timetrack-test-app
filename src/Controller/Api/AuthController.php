<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\Auth\RegisterType;
use App\Traits\AuthServiceAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
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
