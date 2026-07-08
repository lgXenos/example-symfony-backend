<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Исключение для обозначения ошибок 502 (Bad Gateway),
 * когда внешний микросервис или сторонний API вернул некорректный ответ.
 */
class BadGatewayHttpException extends HttpException
{
    /**
     * Конструктор исключения BadGatewayHttpException.
     *
     * @param string $message Сообщение об ошибке.
     * @param \Throwable|null $previous Предыдущее исключение для цепочки логов.
     * @param int $code Внутренний код ошибки.
     * @param array<string, mixed> $headers HTTP-заголовки, которые нужно передать в ответе.
     */
    public function __construct(
        string $message = 'Bad Gateway',
        ?\Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        parent::__construct(Response::HTTP_BAD_GATEWAY, $message, $previous, $headers, $code);
    }
}
