<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\AppWebTestCase;

/**
 * Class ApiDeprecationTest
 * Проверяет корректность работы версионирования и заголовков устаревания.
 */
final class ApiDeprecationTest extends AppWebTestCase
{
    /**
     * Проверяет, что запрос к V1 возвращает заголовки депрекации.
     */
    public function testV1ReturnsDeprecationHeaders(): void
    {
        $authClient = static::createAuthorizedClient()->withAuth();
        // Для самого запроса достаем нативный клиент Symfony
        $client = $authClient->getInternalClient();

        $client->request('GET', '/api/v1/static');

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();
        $this->assertSame('true', $response->headers->get('Deprecation'));
        $this->assertSame(
            '</api/v2/static>; rel="successor-version"',
            $response->headers->get('Link')
        );
    }

    /**
     * Проверяет, что новая версия V2 работает стабильно и не содержит заголовков устаревания.
     */
    public function testV2WorksNormal(): void
    {
        $authClient = static::createAuthorizedClient()->withAuth();
        $client = $authClient->getInternalClient();

        $client->request('GET', '/api/v2/static');

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();
        $this->assertFalse($response->headers->has('Deprecation'));
    }
}
