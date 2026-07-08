<?php

declare(strict_types=1);

namespace App\Controller\V2;

use App\Service\Status\StatusManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class StatusController
 * Предоставляет метаданные о состоянии микросервиса (Версия 2).
 */
#[Route('/api/v2', name: 'api_v2_status_')]
final class StatusController extends AbstractController
{
    public function __construct(
        private readonly StatusManager $statusManager
    ) {
    }

    /**
     * Возвращает обновленную структуру метаданных в версии V2.
     *
     * @return array<string, mixed> Улучшенная структура данных.
     */
    #[Route('/static', name: 'static', methods: ['GET'])]
    public function getStaticData(): array
    {
        // Контроллер V2 жестко просит версию 2
        return $this->statusManager->getStatusForVersion(2);
    }
}
