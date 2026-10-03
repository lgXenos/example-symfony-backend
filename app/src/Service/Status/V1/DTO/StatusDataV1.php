<?php

declare(strict_types=1);

namespace App\Service\Status\V1\DTO;

use App\Contract\ArrayableInterface;

/**
 * Class StatusDataV1
 * DTO для метаданных версии API v1.
 */
final class StatusDataV1 implements ArrayableInterface
{
    /**
     * @param string $framework Название фреймворка
     * @param string $type Тип сервиса
     * @param string $status Статус работы
     */
    public function __construct(
        public readonly string $framework,
        public readonly string $type,
        public readonly string $status,
    ) {
    }

    /**
     * Создает объект DTO из массива.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $framework = $data['framework'] ?? '';
        $type = $data['type'] ?? '';
        $status = $data['status'] ?? '';

        return new self(
            framework: is_scalar($framework) ? (string)$framework : '',
            type: is_scalar($type) ? (string)$type : '',
            status: is_scalar($status) ? (string)$status : '',
        );
    }

    /**
     * Преобразует DTO в массив.
     *
     * @return array{framework: string, type: string, status: string}
     */
    public function toArray(): array
    {
        return [
            'framework' => $this->framework,
            'type' => $this->type,
            'status' => $this->status,
        ];
    }
}
