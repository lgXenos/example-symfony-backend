<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\Service\Auth\AuthManager;
use App\Service\Auth\AuthenticatorInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class AuthManagerTest
 * Изолированный модульный тест для проверки логики выбора версий аутентификации.
 */
final class AuthManagerTest extends TestCase
{
    /**
     * Тест проверяет успешный возврат аутентификатора для зарегистрированных версий.
     */
    public function testGetAuthenticatorForVersionSuccess(): void
    {
        // 1. Настраиваем чистый stub для аутентификатора V1
        $v1Authenticator = $this->createStub(AuthenticatorInterface::class);
        $v1Authenticator->method('getVersion')->willReturn(1);

        // 2. Настраиваем чистый stub для аутентификатора V2
        $v2Authenticator = $this->createStub(AuthenticatorInterface::class);
        $v2Authenticator->method('getVersion')->willReturn(2);

        // Инициализируем менеджер коллекцией заглушек
        $manager = new AuthManager([$v1Authenticator, $v2Authenticator]);

        // Проверяем, что менеджер возвращает именно тот объект, который мы ожидаем
        $actualAuthenticator = $manager->getAuthenticatorForVersion(1);
        $this->assertSame($v1Authenticator, $actualAuthenticator);
    }

    /**
     * Тест проверяет выброс исключения при запросе незарегистрированной версии.
     */
    public function testGetAuthenticatorForVersionThrowsExceptionWhenNotFound(): void
    {
        $manager = new AuthManager([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Authenticator for API v3 is not registered.');

        $manager->getAuthenticatorForVersion(3);
    }
}
