<?php

declare(strict_types=1);

namespace App\Tests;

use App\EventListener\AuthTokenListener;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthorizedBrowserKitClient
 * Декоратор над стандартным KernelBrowser для удобной работы с авторизацией.
 *
 */
final readonly class AuthorizedBrowserKitClient
{
    /**
     * @param KernelBrowser $browser Оригинальный клиент Symfony.
     */
    public function __construct(
        private KernelBrowser $browser
    ) {
    }

    /**
     * Автоматически добавляет микросервисный токен к параметрам сервера.
     *
     * @param string|null $token Если null, берется дефолтный токен из $_ENV.
     * @return self
     */
    public function withAuth(?string $token = null): self
    {
        // 1. Если токен передан в аргумент, используем его.
        // Если нет — берем из $_ENV.
        $resolvedToken = $token ?? ($_ENV['MICROSERVICE_AUTH_TOKEN'] ?? null);

        // 2. Если токена нет ни в аргументе, ни в $_ENV — жестко падаем
        if ($resolvedToken === null) {
            throw new \LogicException(
                'Переменная окружения MICROSERVICE_AUTH_TOKEN не задана. ' .
                'Проверьте файл .env.test или конфигурацию CI.'
            );
        }

        // 3. Защита для PHPStan: проверяем, что в $_ENV пришла именно строка/число, а не массив
        if (!is_scalar($resolvedToken)) {
            throw new \LogicException('MICROSERVICE_AUTH_TOKEN должен быть скалярным значением.');
        }

        $serverKey = 'HTTP_' . str_replace('-', '_', strtoupper(AuthTokenListener::HEADER_NAME));

        // Теперь PHPStan на 100% уверен, что $resolvedToken — это безопасная строка
        $this->browser->setServerParameter($serverKey, (string) $resolvedToken);

        return $this;
    }

    /**
     * Удаляет токен авторизации для проверки гостевого доступа.
     *
     * @return self
     */
    public function withoutAuth(): self
    {
        $serverKey = 'HTTP_' . str_replace('-', '_', strtoupper(AuthTokenListener::HEADER_NAME));
        $this->browser->setServerParameter($serverKey, '');
        //TODO: или мб так
        /*
        if (array_key_exists($serverKey, $this->server)) {
            unset($this->server[$serverKey]);
        }
        */

        return $this;
    }

    public function getInternalClient(): KernelBrowser
    {
        return $this->browser;
    }
}
