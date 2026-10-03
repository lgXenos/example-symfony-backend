<?php

declare(strict_types=1);

namespace App\Service\Status;

use App\Contract\ArrayableInterface;
use App\Contract\VersionedInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Interface StatusProviderInterface
 * Контракт для провайдеров метаданных конкретной версии микросервиса.
 */
#[AutoconfigureTag]
interface StatusProviderInterface extends VersionedInterface
{
    /**
     * Возвращает DTO метаданных для своей версии.
     */
    public function getData(): ArrayableInterface;
}
