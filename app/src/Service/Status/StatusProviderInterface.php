<?php

declare(strict_types=1);

namespace App\Service\Status;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Interface StatusProviderInterface
 * Контракт для провайдеров метаданных конкретной версии микросервиса.
 */
#[AutoconfigureTag]
interface StatusProviderInterface
{
    /**
     * Возвращает версию API, которую поддерживает данный провайдер.
     *
     * @return int Номер версии (например, 1, 2).
     */
    public function getVersion(): int;

    /**
     * Возвращает массив метаданных для своей версии.
     *
     * @return array<string, mixed>|object Ассоциативный массив данных состояния или DTO.
     */
    public function getData(): array|object;
}
