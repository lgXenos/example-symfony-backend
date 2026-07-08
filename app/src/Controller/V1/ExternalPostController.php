<?php

declare(strict_types=1);

namespace App\Controller\V1;

use App\Service\Integration\IntegrationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class ExternalPostController
 * Отвечает за предоставление данных, агрегированных из внешних систем версии V1.
 */
#[Route('/api/v1', name: 'api_v1_external_')]
final class ExternalPostController extends AbstractController
{
    public function __construct(
        private readonly IntegrationManager $integrationManager
    ) {
    }

    /**
     * Получение постов из стороннего API V1.
     *
     * @return array<mixed> Массив данных от внешнего сервиса.
     */
    #[Route('/external-posts', name: 'posts', methods: ['GET'])]
    public function getExternalData(): array
    {
        return $this->integrationManager->getFetcherForVersion(1)->fetchPosts();
    }
}
