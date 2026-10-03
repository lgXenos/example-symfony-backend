<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Класс ExceptionListener перехватывает любые исключения в приложении,
 * логирует критические сбои и отдает клиенту безопасный JSON-ответ.
 */
final class ExceptionListener
{
    /**
     * Конструктор класса.
     * Symfony автоматически связывает %kernel.debug% с режимом APP_ENV=dev.
     */
    public function __construct(
        private LoggerInterface $logger,
        #[Autowire('%kernel.debug%')] private bool $isDebug
    ) {
    }

    /**
     * Перехватывает исключение и формирует понятный для API ответ.
     *
     * @param ExceptionEvent $event
     * @return void
     */
    #[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $previousException = $exception->getPrevious();
        $statusCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'Internal Server Error';
        $validationErrors = null;

        // 1. Ошибка валидации DTO (прямая или завернутая в HttpException)
        if ($exception instanceof ValidationFailedException || $previousException instanceof ValidationFailedException) {
            /** @var ValidationFailedException $validationException */
            $validationException = $exception instanceof ValidationFailedException ? $exception : $previousException;

            $statusCode = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;
            $message = 'Validation Failed';

            $validationErrors = [];
            foreach ($validationException->getViolations() as $violation) {
                $validationErrors[$violation->getPropertyPath()] = $violation->getMessage();
            }
        } // 2. Контролируемая HTTP-ошибка (404, 401, 403, 502)
        elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();

            // Сообщение отдаем клиенту только для "клиентских" кодов (< 500)
            // и для наших собственных исключений - их текст заведомо безопасен.
            // Иначе (например, HttpException с 500 и внутренними деталями) - generic.
            if ($statusCode < 500 || $this->isOwnException($exception)) {
                $message = $exception->getMessage();
            } else {
                $this->logger->error('Server error: ' . $exception->getMessage(), [
                    'exception' => $exception,
                ]);
            }
        } // 3. Непредвиденный критический сбой (ошибка в коде, Fatal Error)
        else {
            $this->logger->error('CRITICAL BUG: ' . $exception->getMessage(), [
                'exception' => $exception,
                'trace' => $exception->getTraceAsString()
            ]);
        }

        // Базовая структура ответа
        $errorResponse = [
            'status' => 'error',
            'timestamp' => time(),
            'error' => [
                'code' => $statusCode,
                'message' => $message,
            ]
        ];

        if ($validationErrors !== null) {
            $errorResponse['error']['violations'] = $validationErrors;
        }

        // если %kernel.debug% равен true (при APP_ENV=dev)
        if ($this->isDebug) {
            $errorResponse['dev_info'] = [
                'previous_message' => $exception->getPrevious()?->getMessage(),
                'exception_class' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        $event->setResponse(new JsonResponse($errorResponse, $statusCode));
    }

    /**
     * Наши собственные исключения (UserFriendlyException, BadGatewayHttpException)
     * создаются осознанно и их сообщения безопасны для отдачи клиенту.
     */
    private function isOwnException(\Throwable $exception): bool
    {
        return $exception instanceof \App\Exception\UserFriendlyException
            || $exception instanceof \App\Exception\BadGatewayHttpException;
    }
}
