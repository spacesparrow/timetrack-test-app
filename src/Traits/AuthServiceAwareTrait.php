<?php

declare(strict_types=1);

namespace App\Traits;

use App\Service\AuthService;

trait AuthServiceAwareTrait
{
    private AuthService $authService;

    /** @required */
    public function setAuthService(AuthService $authService): void
    {
        $this->authService = $authService;
    }

    protected function getAuthService(): AuthService
    {
        return $this->authService;
    }
}