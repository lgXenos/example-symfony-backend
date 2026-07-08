<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AppWebTestCase
 * Базовый класс для всех функциональных HTTP-тестов приложения.
 */
abstract class AppWebTestCase extends WebTestCase
{
    /**
     * Фабричный метод, возвращающий наш прокачанный клиент.
     *
     * @param array<mixed> $options
     * @param array<mixed> $server
     * @return AuthorizedBrowserKitClient
     */
    protected static function createAuthorizedClient(array $options = [], array $server = []): AuthorizedBrowserKitClient
    {
        // Создаем стандартный клиент Symfony
        $kernelBrowser = parent::createClient($options, $server);

        // Оборачиваем его в наш декоратор
        return new AuthorizedBrowserKitClient($kernelBrowser);
    }

    /**
     * Метод одновременной проверки, что это строчка и она не пустая
     *
     * @param mixed $value
     * @param string $message
     * @return void
     */
    protected function assertNotEmptyString(mixed $value, string $message = ''): void
    {
        $this->assertIsString($value, $message);
        $this->assertNotEmpty($value, $message);
    }
}
