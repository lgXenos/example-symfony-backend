<?php

declare(strict_types=1);

namespace App\Service\Status;

use App\Service\Support\VersionedRegistry;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Class StatusManager
 * Централизованный сервис управления версиями метаданных (Паттерн Стратегия).
 */
final class StatusManager
{
    /** @var VersionedRegistry<StatusProviderInterface> */
    private readonly VersionedRegistry $registry;

    /**
     * @param iterable<StatusProviderInterface> $providers Коллекция всех провайдеров из DI-контейнера.
     */
    public function __construct(
        #[AutowireIterator(StatusProviderInterface::class)] iterable $providers
    ) {
        $this->registry = new VersionedRegistry($providers, 'Status provider');
    }

    /**
     * Возвращает метаданные для указанной версии API.
     *
     * @param int $version Номер запрашиваемой версии.
     * @return array<string, mixed>
     *
     * @throws \InvalidArgumentException Если провайдер для указанной версии не зарегистрирован.
     */
    public function getStatusForVersion(int $version): array
    {
        // Контракт StatusProviderInterface гарантирует наличие toArray() у DTO
        return $this->registry->get($version)->getData()->toArray();
    }
}
