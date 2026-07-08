<?php

declare(strict_types=1);

namespace App\Service\Auth\V1;

use App\Exception\UserFriendlyException;
use App\Service\Auth\AuthenticatorInterface;
use App\Service\Auth\AuthInputInterface;
use App\Service\Auth\V1\DTO\LoginInput;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Class EnvAuthenticator
 * Аутентификация версии V1 со строгой типизацией DTO.
 */
final class EnvAuthenticator implements AuthenticatorInterface
{
    public function __construct(
        #[Autowire('%env(MICROSERVICE_LOGIN)%')] private readonly string $expectedLogin,
        #[Autowire('%env(MICROSERVICE_PASSWORD)%')] private readonly string $expectedPassword,
        #[Autowire('%env(MICROSERVICE_AUTH_TOKEN)%')] private readonly string $authToken
    ) {
    }

    public function getVersion(): int
    {
        return 1;
    }

    /**
     * Бизнес-логика V1 работает со строгим объектом DTO.
     *
     * @throws UserFriendlyException
     */
    public function authenticate(AuthInputInterface $input): string
    {
        if ($input instanceof LoginInput) {
            if ($input->login !== $this->expectedLogin || $input->password !== $this->expectedPassword) {
                throw new UserFriendlyException('Invalid login or password.');
            }
        } else {
            throw new UserFriendlyException('Invalid input type for V1 authentication.');
        }

        return $this->authToken;
    }
}
