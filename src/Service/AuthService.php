<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthService
{
    /** @var UserRepository  */
    private UserRepository $userRepository;

    /** @var UserPasswordEncoderInterface */
    private UserPasswordEncoderInterface $userPasswordEncoder;

    /**
     * AuthService constructor.
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function checkEmailUsed(string $email): bool
    {
        return (bool)$this->userRepository->findOneByEmail($email);
    }

    /**
     * @param User $user
     */
    public function encodeUserPassword(User $user): void
    {
        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $user->getPassword()));
    }
}