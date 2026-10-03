<?php

declare(strict_types=1);

namespace App\Contract;

/**
 * Interface ArrayableInterface
 * Контракт DTO, которые умеют конвертировать себя в массив для JSON-ответа.
 * Гарантирует наличие toArray() на этапе компиляции, а не в рантайме.
 */
interface ArrayableInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
