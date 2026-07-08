<?php

declare(strict_types=1);

namespace App\Service\Status;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Class StatusManager
 * Централизованный сервис управления версиями метаданных (Паттерн Стратегия).
 */
final class StatusManager
{
    /**
     * @var array<int, StatusProviderInterface> Пул провайдеров, индексированный версиями.
     */
    private readonly array $providers;

    /**
     * @param iterable<StatusProviderInterface> $providers Коллекция всех провайдеров из DI-контейнера.
     */
    public function __construct(
        #[AutowireIterator(StatusProviderInterface::class)] iterable $providers
    ) {
        // Заполняем карту провайдеров для поиска по ключу версии
        $providerMap = [];
        foreach ($providers as $provider) {
            $providerMap[$provider->getVersion()] = $provider;
        }
        $this->providers = $providerMap;
    }

    /**
     * Возвращает метаданные для указанной версии API.
     *
     * @param int $version Номер запрашиваемой версии.
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException Если провайдер для указанной версии не зарегистрирован.
     */
    public function getStatusForVersion(int $version): array
    {
        if (!isset($this->providers[$version])) {
            throw new InvalidArgumentException(
                sprintf('Status provider for version API v%d is not registered.', $version)
            );
        }

        $data = $this->providers[$version]->getData();

        // Если провайдер возвращает DTO с методом toArray(), конвертируем в массив
        if (is_object($data) && method_exists($data, 'toArray')) {
            return $data->toArray();
        }

        throw new InvalidArgumentException('Status provider not implement method "toArray"');
    }
}
