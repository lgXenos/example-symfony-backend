<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ApiDeprecationListener
 * Автоматически добавляет HTTP-заголовки устаревания на основе конфигурации роута.
 */
#[AsEventListener(event: KernelEvents::RESPONSE, priority: 0)]
final class ApiDeprecationListener
{
    /**
     * Перехватывает ответ и анализирует параметры вызванного роута.
     *
     * @param ResponseEvent $event Объект события ответа Symfony.
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        // Игнорируем подзапросы
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        // Проверяем, есть ли в текущем роуте маркер депрекации
        if ($request->attributes->getBoolean('_api_deprecated')) {
            $response->headers->set('Deprecation', 'true');

            /** @var string|null $successor */
            $successor = $request->attributes->get('_api_successor');
            if ($successor !== null) {
                // Формируем заголовок Link по стандарту RFC 8594
                $response->headers->set(
                    'Link',
                    sprintf('<%s>; rel="successor-version"', $successor)
                );
            }
        }
    }
}
