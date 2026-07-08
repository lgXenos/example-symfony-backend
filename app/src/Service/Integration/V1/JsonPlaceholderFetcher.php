<?php

declare(strict_types=1);

namespace App\Service\Integration\V1;

use App\Exception\BadGatewayHttpException;
use App\Service\Integration\PostFetcherInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class JsonPlaceholderFetcher
 * Реализация получения постов через провайдер JSONPlaceholder для API V1.
 */
final class JsonPlaceholderFetcher implements PostFetcherInterface
{
    /**
     * @param HttpClientInterface $typicodeClient Компонент Symfony для HTTP-запросов.
     */
    public function __construct(
        private readonly HttpClientInterface $typicodeClient,
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
