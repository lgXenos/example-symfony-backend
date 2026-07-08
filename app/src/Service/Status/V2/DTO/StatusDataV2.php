<?php

declare(strict_types=1);

namespace App\Service\Status\V2\DTO;

/**
 * Class StatusDataV2
 * DTO для метаданных версии API v2.
 */
final class StatusDataV2
{
    /**
     * @param array<string, string> $engine Данные о движке
     * @param array<string, string> $meta Метаданные сервиса
     */
    public function __construct(
        public readonly array $engine,
        public readonly array $meta,
    ) {
    }

    /**
     * Создает объект DTO из массива.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $engineData = isset($data['engine']) && is_array($data['engine']) ? $data['engine'] : [];
        $metaData = isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [];

        $engine = [];
        foreach ($engineData as $key => $value) {
            $engine[(string)$key] = is_scalar($value) ? (string)$value : '';
        }

        $meta = [];
        foreach ($metaData as $key => $value) {
            $meta[(string)$key] = is_scalar($value) ? (string)$value : '';
        }

        return new self(
            engine: $engine,
            meta: $meta,
        );
    }

    /**
     * Преобразует DTO в массив.
     *
     * @return array{engine: array<string, string>, meta: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'engine' => $this->engine,
            'meta' => $this->meta,
        ];
    }
}
