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
     * Конструктор принимает токен и список публичных роутов из app/config/services.yaml.
     *
     * @param string   $secretToken
     * @param string[] $publicRoutes Явный белый список роутов, не требующих токена.
     */
    public function __construct(
        private readonly string $secretToken,
        private readonly array $publicRoutes = []
    ) {
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

        // Если роут не определен (например, 404 ошибка) или он в белом списке публичных - пропускаем
        if (
            !is_string($currentRoute)
            ||
            in_array($currentRoute, $this->publicRoutes, true)
        ) {
            return;
        }

        $token = $request->headers->get(self::HEADER_NAME) ?? '';

        $this->validateToken($token);
    }

    /**
     * Инкапсулирует внутреннюю логику валидации API-токена.
     * Сравнение через hash_equals - защита от timing-атак.
     *
     * @param string $token Токен, полученный из заголовков запроса.
     * @return void
     *
     * @throws AccessDeniedHttpException Если переданный токен не совпадает с эталонным.
     */
    private function validateToken(string $token): void
    {
        if (!hash_equals($this->secretToken, $token)) {
            throw new AccessDeniedHttpException('Invalid or missing auth token.');
        }
    }
}
