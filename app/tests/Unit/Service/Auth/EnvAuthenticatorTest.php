<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\Exception\UserFriendlyException;
use App\Service\Auth\V1\DTO\LoginInput;
use App\Service\Auth\V1\EnvAuthenticator;
use PHPUnit\Framework\TestCase;

/**
 * Class EnvAuthenticatorTest
 * Проверяет изолированную бизнес-логику сервиса авторизации V1.
 */
final class EnvAuthenticatorTest extends TestCase
{
    private const string EXPECTED_LOGIN = 'admin';
    private const string EXPECTED_PASSWORD = 'secret_password';
    private const string AUTH_TOKEN = 'valid_token_123';

    private EnvAuthenticator $authenticator;

    /**
     * Настройка окружения перед каждым тестом.
     */
    protected function setUp(): void
    {
        $this->authenticator = new EnvAuthenticator(
            self::EXPECTED_LOGIN,
            self::EXPECTED_PASSWORD,
            self::AUTH_TOKEN
        );
    }

    /**
     * Проверяет, что провайдер возвращает корректную версию API.
     */
    public function testGetVersionReturnsCorrectValue(): void
    {
        $this->assertSame(1, $this->authenticator->getVersion());
    }

    /**
     * Проверяет успешную аутентификацию с корректными учетными данными.
     */
    public function testAuthenticateSuccess(): void
    {
        // Передаем строго типизированный DTO V1
        $input = new LoginInput(self::EXPECTED_LOGIN, self::EXPECTED_PASSWORD);

        $token = $this->authenticator->authenticate($input);

        $this->assertSame(self::AUTH_TOKEN, $token);
    }

    /**
     * Проверяет, что при неверном логине или пароле выбрасывается кастомное исключение.
     */
    public function testAuthenticateInvalidCredentialsThrowsException(): void
    {
        // Передаем строго типизированный DTO V1 с неверными данными
        $input = new LoginInput('wrong_login', 'wrong_password');

        $this->expectException(UserFriendlyException::class);
        $this->expectExceptionMessage('Invalid login or password.');

        $this->authenticator->authenticate($input);
    }
}
