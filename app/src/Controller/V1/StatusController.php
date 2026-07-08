<?php

declare(strict_types=1);

namespace App\Controller\V1;

use App\Service\Status\StatusManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class StatusController
 * Предоставляет метаданные о состоянии микросервиса (Версия 1).
 */
#[Route('/api/v1', name: 'api_v1_status_')]
final class StatusController extends AbstractController
{
    public function __construct(
        private readonly StatusManager $statusManager
    ) {
    }

    /**
     * Возвращает статические метаданные.
     * Эндпоинт помечен как устаревший (deprecated).
     *
     * @return array<string, mixed> Данные инфраструктуры.
     */
    #[Route(
        path: '/static',
        name: 'static',
        defaults: ['_api_deprecated' => true, '_api_successor' => '/api/v2/static'],
        methods: ['GET']
    )]
    public function getStaticData(): array
    {
        // Контроллер V1 жестко просит версию 1
        return $this->statusManager->getStatusForVersion(1);
    }
}
