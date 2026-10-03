<?php

declare(strict_types=1);

namespace App\Contract;

/**
 * Interface VersionedInterface
 * Контракт для сервисов, привязанных к конкретной версии API.
 * Позволяет универсальному VersionedRegistry строить карту "версия => сервис".
 */
interface VersionedInterface
{
    /**
     * Возвращает версию API, которую поддерживает данный сервис.
     */
    public function getVersion(): int;
}
