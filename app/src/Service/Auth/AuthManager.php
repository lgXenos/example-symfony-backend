<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Service\Support\VersionedRegistry;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Class AuthManager
 * Фабрика для получения сервиса аутентификации нужной версии.
 */
final class AuthManager
{
    /** @var VersionedRegistry<AuthenticatorInterface> */
    private readonly VersionedRegistry $registry;

    /**
     * @param iterable<AuthenticatorInterface> $authenticators
     */
    public function __construct(
        #[AutowireIterator(AuthenticatorInterface::class)] iterable $authenticators
    ) {
        $this->registry = new VersionedRegistry($authenticators, 'Authenticator');
    }

    /**
     * Возвращает конкретный сервис аутентификации для указанной версии.
     *
     * @param int $version
     * @return AuthenticatorInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getAuthenticatorForVersion(int $version): AuthenticatorInterface
    {
        return $this->registry->get($version);
    }
}
