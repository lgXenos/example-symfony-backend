<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;


/**
 * Класс AuthTokenListener отвечает за проверку API-токенов во входящих запросах.
 */
final class AuthTokenListener
{
    public const HEADER_NAME = 'X-Microservice-Token';

    /**
     * Конструктор принимает токен, проброшенный из конфигурации app/config/services.yaml
     *
     * @param string $secretToken
     */
    public function __construct(private readonly string $secretToken)
    {
    }

    /**
     * Перехватывает входящий HTTP-запрос для проверки авторизации.
     *
     * Приоритет 20 == запуск после определения роута, но до того, как запустится контроллер
     *
     * @param RequestEvent $event Объект события текущего HTTP-запроса.
     * @return void
     *
     * @throws AccessDeniedHttpException Если токен не прошел валидацию.
     */
    #[AsEventListener(event: KernelEvents::REQUEST, priority: 20)]
    public function onKernelRequest(RequestEvent $event): void
    {
        // Обрабатываем только главный запрос (игнорируем подзапросы Symfony)
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Получаем имя текущего роута из атрибутов запроса Symfony
        $currentRoute = $request->attributes->get('_route');

        // Если роут не определен (например, 404 ошибка), или это логин - пропускаем
        if (
            !is_string($currentRoute)
            ||
            preg_match('#^api_v[\d]+_auth_login$#', $currentRoute) === 1
        ) {
            return;
        }

        $token = $request->headers->get(self::HEADER_NAME) ?? '';

        $this->validateToken($token);
    }

    /**
     * Инкапсулирует внутреннюю логику валидации API-токена.
     *
     * @param string $token Токен, полученный из заголовков запроса.
     * @return void
     *
     * @throws AccessDeniedHttpException Если переданный токен не совпадает с эталонным.
     */
    protected function validateToken(string $token): void
    {
        if ($token !== $this->secretToken) {
            throw new AccessDeniedHttpException('Invalid or missing auth token.');
        }
    }
}
