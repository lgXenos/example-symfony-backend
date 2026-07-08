<?php

declare(strict_types=1);

namespace App\Service\Integration;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Class IntegrationManager
 * Фабрика для получения сервисов интеграции нужной версии.
 */
final class IntegrationManager
{
    /**
     * @var array<int, PostFetcherInterface>
     */
    private readonly array $fetchers;

    /**
     * @param iterable<PostFetcherInterface> $fetchers
     */
    public function __construct(
        #[AutowireIterator(PostFetcherInterface::class)] iterable $fetchers
    ) {
        $fetchersMap = [];
        foreach ($fetchers as $fetcher) {
            $fetchersMap[$fetcher->getVersion()] = $fetcher;
        }
        $this->fetchers = $fetchersMap;
    }

    /**
     * Возвращает фетчер постов для конкретной версии.
     *
     * @throws InvalidArgumentException
     */
    public function getFetcherForVersion(int $version): PostFetcherInterface
    {
        if (!isset($this->fetchers[$version])) {
            throw new InvalidArgumentException(sprintf('Post fetcher for API v%d is not registered.', $version));
        }

        return $this->fetchers[$version];
    }
}
