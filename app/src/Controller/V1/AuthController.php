<?php

declare(strict_types=1);

namespace App\Controller\V1;

use App\Service\Auth\AuthManager;
use App\Service\Auth\V1\DTO\LoginInput;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1', name: 'api_v1_auth_')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthManager $authManager
    ) {
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(#[MapRequestPayload] LoginInput $input): string
    {
        $authenticator = $this->authManager->getAuthenticatorForVersion(1);

        return $authenticator->authenticate($input);
    }
}
