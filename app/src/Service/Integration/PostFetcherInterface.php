<?php

declare(strict_types=1);

namespace App\Service\Integration;

use App\Contract\VersionedInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Interface PostFetcherInterface
 * Контракт для получения публикаций из внешних источников с поддержкой версионирования.
 */
#[AutoconfigureTag]
interface PostFetcherInterface extends VersionedInterface
{
    /**
     * Получает список постов из внешнего API.
     *
     * @return array<mixed> Данные в виде ассоциативного массива.
     *
     * @throws \App\Exception\BadGatewayHttpException Если внешний сервис недоступен.
     */
    public function fetchPosts(): array;
}
