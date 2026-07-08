<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AuthControllerTest
 * Проверяет HTTP-эндпоинт авторизации, корректность роутинга и валидацию.
 */
final class AuthControllerTest extends WebTestCase
{
    /**
     * Проверяет успешный POST-запрос на авторизацию.
     */
    public function testLoginSuccess(): void
    {
        // 1. Инициализируем клиент
        $client = static::createClient();

        // 2. Безопасно читаем переменные окружения, изолированные внутри Symfony Kernel
        $login = $_SERVER['MICROSERVICE_LOGIN'] ?? $_ENV['MICROSERVICE_LOGIN'] ?? 'admin';
        $password = $_SERVER['MICROSERVICE_PASSWORD'] ?? $_ENV['MICROSERVICE_PASSWORD'] ?? 'secret';
        $expectedToken = $_SERVER['MICROSERVICE_AUTH_TOKEN'] ?? $_ENV['MICROSERVICE_AUTH_TOKEN'] ?? '';


        $client->request(
            method: 'POST',
            uri: '/api/v1/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'login' => $login ?: 'admin',
                'password' => $password ?: 'secret',
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseIsSuccessful();

        /** @var string $responseContent */
        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);

        // Проверяем формат ответа, который гарантирует ResponseFormatterListener
        $this->assertSame('success', $responseData['status']);
        $this->assertSame($expectedToken, $responseData['data']);
    }

    /**
     * Проверяет валидацию пустых полей (код ответа 422).
     */
    public function testLoginValidationFailed(): void
    {
        $client = static::createClient();

        $client->request(
            method: 'POST',
            uri: '/api/v1/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'login' => '',
                'password' => '',
            ], JSON_THROW_ON_ERROR)
        );

        // Ошибка валидации Symfony возвращает статус HTTP 422 Unprocessable Entity
        $this->assertResponseStatusCodeSame(422);
    }
}
