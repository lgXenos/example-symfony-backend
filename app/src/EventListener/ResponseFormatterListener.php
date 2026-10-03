<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Класс ResponseFormatterListener отвечает за автоматическое форматирование
 * результатов работы контроллеров в единую структуру JSON-ответа.
 *
 * Контракт: контроллеры API возвращают массив, scalar или DTO (public-свойства
 * сериализуются JsonResponse). Ответы, уже являющиеся Response, не трогаем.
 */
final class ResponseFormatterListener
{
    /**
     * Оборачивает результат работы контроллера в конверт API.
     *
     * @param ViewEvent $event
     * @return void
     */
    #[AsEventListener(event: KernelEvents::VIEW, priority: 100)]
    public function onKernelView(ViewEvent $event): void
    {
        // Конверт навешиваем только на ответы главного запроса
        if (!$event->isMainRequest()) {
            return;
        }

        $result = $event->getControllerResult();

        // Контроллер сам собрал готовый Response - не вмешиваемся,
        // чтобы не получить два разных формата ответа в одном API.
        if ($result instanceof Response) {
            return;
        }

        $customFormat = [
            'status' => 'success',
            'timestamp' => time(),
            'data' => $result
        ];

        // Передаем структуру в JsonResponse, он сам сериализует всё в JSON
        $event->setResponse(new JsonResponse($customFormat));
    }
}
