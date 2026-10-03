<?php

declare(strict_types=1);

namespace App\Service\Integration\V1;

use App\Exception\BadGatewayHttpException;
use App\Service\Integration\PostFetcherInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class JsonPlaceholderFetcher
 * Реализация получения постов через провайдер JSONPlaceholder для API V1.
 * Ответ кэшируется (cache.app): внешний API медленный и нестабильный,
 * а одинаковые ответы не имеет смысла запрашивать повторно.
 */
final class JsonPlaceholderFetcher implements PostFetcherInterface
{
    private const CACHE_KEY = 'external_posts.v1';

    private const CACHE_TTL_SECONDS = 60;

    /**
     * @param HttpClientInterface $typicodeClient Компонент Symfony для HTTP-запросов.
     * @param CacheInterface $cache Кэш-контракт (по умолчанию cache.app).
     */
    public function __construct(
        private readonly HttpClientInterface $typicodeClient,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getVersion(): int
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function fetchPosts(): array
    {
        /**
         * get() отдаст закэшированное значение или выполнит колбэк и сохранит результат.
         * Если колбэк бросает исключение (внешний API недоступен) - в кэш ничего не пишется,
         * и следующий запрос снова попробует достучаться до API.
         */
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): array {
            $item->expiresAfter(self::CACHE_TTL_SECONDS);
            return $this->fetchFromApi();
        });
    }

    /**
     * Прямой запрос к внешнему API.
     *
     * @return array<mixed>
     */
    private function fetchFromApi(): array
    {
        try {
            $response = $this->typicodeClient->request('GET', '/posts/');

            return $response->toArray();
        } catch (\Throwable $e) {
            throw new BadGatewayHttpException(
                message: 'External API is temporary unavailable or returned invalid JSON.',
                previous: $e
            );
        }
    }
}
