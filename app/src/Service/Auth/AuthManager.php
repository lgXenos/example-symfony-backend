<?php

declare(strict_types=1);

namespace App\Service\Auth;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Class AuthManager
 * Фабрика для получения сервиса аутентификации нужной версии.
 */
final class AuthManager
{
    /**
     * @var array<int, AuthenticatorInterface>
     */
    private readonly array $authenticators;

    /**
     * @param iterable<AuthenticatorInterface> $authenticators
     */
    public function __construct(
        #[AutowireIterator(AuthenticatorInterface::class)] iterable $authenticators
    ) {
        $authenticatorsMap = [];
        foreach ($authenticators as $authenticator) {
            $authenticatorsMap[$authenticator->getVersion()] = $authenticator;
        }
        $this->authenticators = $authenticatorsMap;
    }

    /**
     * Возвращает конкретный сервис аутентификации для указанной версии.
     *
     * @param int $version
     * @return AuthenticatorInterface
     *
     * @throws InvalidArgumentException
     */
    public function getAuthenticatorForVersion(int $version): AuthenticatorInterface
    {
        if (!isset($this->authenticators[$version])) {
            throw new InvalidArgumentException(sprintf('Authenticator for API v%d is not registered.', $version));
        }

        return $this->authenticators[$version];
    }
}
