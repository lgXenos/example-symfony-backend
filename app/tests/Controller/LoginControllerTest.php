<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Класс LoginControllerTest проверяет сценарии авторизации метода /api/v1/login.
 */
final class LoginControllerTest extends AppWebTestCase
{
    /**
     * Проверяет успешную авторизацию с валидными учетными данными из .env.
     */
    public function testLoginSuccess(): void
    {
        // 1. Создаем клиент, сбрасывая дефолтный токен авторизации для чистоты теста
        $authClient = static::createAuthorizedClient()->withoutAuth();
        $client = $authClient->getInternalClient();

        // 2. Получаем данные из окружения и страхуемся от их отсутствия
        $expectedLogin = $_ENV['MICROSERVICE_LOGIN'] ?? '';
        $expectedPassword = $_ENV['MICROSERVICE_PASSWORD'] ?? '';
        $expectedToken = $_ENV['MICROSERVICE_AUTH_TOKEN'] ?? '';

        $this->assertNotEmptyString($expectedLogin, 'MICROSERVICE_LOGIN env variable is empty.');
        $this->assertNotEmptyString($expectedPassword, 'MICROSERVICE_PASSWORD env variable is empty.');
        $this->assertNotEmptyString($expectedToken, 'MICROSERVICE_AUTH_TOKEN env variable is empty.');

        // 3. Выполняем POST запрос с правильным CGI-ключом CONTENT_TYPE
        $client->request(
            method: 'POST',
            uri: '/api/v1/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'login' => $expectedLogin,
                'password' => $expectedPassword,
            ], JSON_THROW_ON_ERROR)
        );

        // 4. Проверяем успешность HTTP-статуса (200 OK)
        $this->assertResponseIsSuccessful();

        // 5. Безопасно извлекаем тело ответа, удовлетворяя требования PHPStan
        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent, 'Response content is not a valid string.');
        $this->assertNotEmpty($responseContent);

        /** @var array{status?: string, data?: mixed, timestamp?: mixed} $responseData */
        $responseData = json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);

        // 6. Проверяем структуру ответа
        $this->assertSame('success', $responseData['status'] ?? null);
        $this->assertSame($expectedToken, $responseData['data'] ?? null);
        $this->assertArrayHasKey('timestamp', $responseData, 'Response envelope is missing "timestamp" field.');
    }

    /**
     * Проверяет поведение системы при передаче некорректных форматов данных (ошибка валидации DTO).
     */
    public function testLoginValidationError(): void
    {
        $authClient = static::createAuthorizedClient()->withoutAuth();
        $client = $authClient->getInternalClient();

        // Отправляем заведомо некорректный формат: слишком короткий логин и пустой пароль
        $client->request(
            method: 'POST',
            uri: '/api/v1/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'login' => 'Ro',
                'password' => '',
            ], JSON_THROW_ON_ERROR)
        );

        // 1. Ожидаем код 422 Unprocessable Entity вместо 401
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $responseContent = $client->getResponse()->getContent();
        $this->assertIsString($responseContent);
        $this->assertNotEmpty($responseContent);
        $responseData = json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertIsArray($responseData['error']);
        $this->assertIsArray($responseData['error']['violations']);

        // 2. Проверяем структуру ответа ExceptionListener на ошибку валидации
        $this->assertSame('error', $responseData['status']);
        $this->assertSame(422, $responseData['error']['code'] ?? null);
        $this->assertSame('Validation Failed', $responseData['error']['message'] ?? null);

        // 3. Проверяем английские сообщения об ошибках конкретных полей
        $this->assertArrayHasKey('violations', $responseData['error']);
        $this->assertSame('Login must be at least 3 characters long.', $responseData['error']['violations']['login'] ?? null);
        $this->assertSame('Password cannot be blank.', $responseData['error']['violations']['password'] ?? null);
    }
}
