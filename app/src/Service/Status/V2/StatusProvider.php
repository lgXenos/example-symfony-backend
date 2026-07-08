<?php

declare(strict_types=1);

namespace App\Service\Status\V2;

use App\Service\Status\StatusProviderInterface;
use App\Service\Status\V2\DTO\StatusDataV2;

/**
 * Class V2StatusProvider
 * Провайдер статических метаданных для API версии 2 (с измененной структурой).
 */
final class StatusProvider implements StatusProviderInterface
{

    public function getVersion(): int
    {
        return 2;
    }

    public function getData(): StatusDataV2
    {
        return new StatusDataV2(
            engine: [
                'framework' => 'Symfony',
                'version' => '7.0',
            ],
            meta: [
                'type' => 'Microservice',
                'status' => 'active',
            ],
        );
    }
}
