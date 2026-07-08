<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Класс ResponseFormatterListener отвечает за автоматическое форматирование
 * результатов работы контроллеров в единую структуру JSON-ответа.
 */
final class ResponseFormatterListener
{
    /**
     * Перехватывает любой результат работы контроллера и оборачивает его в конверт API.
     *
     * @param ViewEvent $event
     * @return void
     */
    #[AsEventListener(event: KernelEvents::VIEW, priority: 100)]
    public function onKernelView(ViewEvent $event): void
    {
        // Просто берем то, что вернул контроллер, без проверок типов
        $result = $event->getControllerResult();

        $customFormat = [
            'status' => 'success',
            'timestamp' => time(),
            'data' => $result
        ];

        // Передаем структуру в JsonResponse, он сам сериализует всё в JSON
        $event->setResponse(new JsonResponse($customFormat));
    }
}
