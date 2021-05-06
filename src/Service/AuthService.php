<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;

class AuthService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function checkEmailUsed(string $email): bool
    {
        return (bool)$this->userRepository->findOneByEmail($email);
    }
}