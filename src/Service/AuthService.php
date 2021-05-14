<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthService
{
    private UserRepository $userRepository;

    private UserPasswordEncoderInterface $userPasswordEncoder;

    /**
     * AuthService constructor.
     */
    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function checkEmailUsed(string $email): bool
    {
        /*
         * Select user with provided email from DB
         */
        return (bool) $this->userRepository->findOneByEmail($email);
    }

    public function encodeUserPassword(User $user): void
    {
        /*
         * Encode plain user password
         */
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $user->getPassword()));
    }
}
