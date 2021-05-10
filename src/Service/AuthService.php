<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;

class AuthService
{
    /** @var UserRepository  */
    private UserRepository $userRepository;

    /**
     * AuthService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function checkEmailUsed(string $email): bool
    {
        return (bool)$this->userRepository->findOneByEmail($email);
    }
}