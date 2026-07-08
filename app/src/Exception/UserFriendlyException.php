<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Сахар для удобных читабельных ошибок. Наследовались от HttpException - получили проброс сразу наружу
 */
class UserFriendlyException extends HttpException
{
    public function __construct(string $message = 'Business logic error', int $statusCode = 400)
    {
        parent::__construct($statusCode, $message);
    }
}
