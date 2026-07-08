<?php

declare(strict_types=1);

namespace App\Service\Auth;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Маркерный интерфейс для стратегий аутентификации.
 * Используется для динамической регистрации версий в DI-контейнере.
 */
#[AutoconfigureTag]
interface AuthenticatorInterface
{
    /**
     * Возвращает версию API, которую поддерживает данный сервис.
     *
     * @return int
     */
    public function getVersion(): int;

    /**
     * Авторизация по входным параметрам
     *
     * @param AuthInputInterface $input
     * @return string
     */
    public function authenticate(AuthInputInterface $input): string;
}
