<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Contract\VersionedInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Маркерный интерфейс для стратегий аутентификации.
 * Используется для динамической регистрации версий в DI-контейнере.
 */
#[AutoconfigureTag]
interface AuthenticatorInterface extends VersionedInterface
{
    /**
     * Авторизация по входным параметрам
     *
     * @param AuthInputInterface $input
     * @return string
     */
    public function authenticate(AuthInputInterface $input): string;
}
