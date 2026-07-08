<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Class ExternalPostControllerTest
 * Проверяет работу с внешним API с использованием изоляции сети.
 */
final class ExternalPostControllerTest extends AppWebTestCase
{
    /**
     * Проверяет успешное проксирование данных от внешнего сервиса.
     */
    public function testGetExternalPostsSuccess(): void
    {
        // 1. Создаем авторизованный клиент
        $authClient = static::createAuthorizedClient()->withAuth();
        $client = $authClient->getInternalClient();

        // 2. Подготавливаем фейковые данные для изоляции сети
        $mockData = [['userId' => 1, 'id' => 1, 'title' => 'Test Post', 'body' => 'Hello World']];
        $mockResponse = new MockResponse(json_encode($mockData, JSON_THROW_ON_ERROR), [
            'http_code' => 200,
            'response_headers' => ['Content-Type' => 'application/json'],
        ]);

        // 3. Извлекаем наш зарегистрированный мок-клиент по строковому ID
        $mockHttpClient = static::getContainer()->get('test.external_post_client');

        // Защитная проверка типа для PHPStan
        $this->assertInstanceOf(MockHttpClient::class, $mockHttpClient);

        // Динамически подставляем фабрику ответов для текущего теста
        $mockHttpClient->setResponseFactory($mockResponse);

        // 4. Выполняем запрос к нашему микросервису
        $client->request(method: 'GET', uri: '/api/v1/external-posts');

        $this->assertResponseIsSuccessful();

        /** @var string $responseContent */
        $responseContent = $client->getResponse()->getContent();

        /** @var array{status: string, data: array<mixed>} $responseData */
        $responseData = json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);

        // 5. Проверяем, что микросервис вернул ИМЕННО наши фейковые данные, а не данные из интернета
        $this->assertSame('success', $responseData['status']);
        $this->assertSame($mockData, $responseData['data']);
    }
}
