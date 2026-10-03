<?php

declare(strict_types=1);

namespace App\Service\Support;

use App\Contract\VersionedInterface;
use InvalidArgumentException;

/**
 * Class VersionedRegistry
 * Универсальный реестр версионированных сервисов: строит карту "версия => сервис"
 * и раздает их по номеру версии. Устраняет дублирование этой логики в менеджерах доменов.
 *
 * @template T of VersionedInterface
 */
final class VersionedRegistry
{
    /** @var array<int, T> Пул сервисов, индексированный версиями. */
    private readonly array $items;

    /**
     * @param iterable<T> $items
     * @param string $label Человекочитаемое имя для сообщения об ошибке (например, "Authenticator")
     */
    public function __construct(iterable $items, private readonly string $label = 'Service')
    {
        $map = [];
        foreach ($items as $item) {
            $map[$item->getVersion()] = $item;
        }

        $this->items = $map;
    }

    /**
     * Возвращает сервис, обслуживающий указанную версию API.
     *
     * @return T
     *
     * @throws InvalidArgumentException Если для версии не зарегистрирован ни один сервис.
     */
    public function get(int $version): VersionedInterface
    {
        if (!isset($this->items[$version])) {
            throw new InvalidArgumentException(
                sprintf('%s for API v%d is not registered.', $this->label, $version)
            );
        }

        return $this->items[$version];
    }
}
