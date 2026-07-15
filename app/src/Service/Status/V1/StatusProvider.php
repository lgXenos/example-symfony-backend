<?php

declare(strict_types=1);

namespace App\Service\Status\V1;

use App\Service\Status\StatusProviderInterface;
use App\Service\Status\V1\DTO\StatusDataV1;

/**
 * Class StatusProvider
 * Провайдер статических метаданных для API версии 1.
 */
final class StatusProvider implements StatusProviderInterface
{
    public function getVersion(): int
    {
        return 1;
    }

    public function getData(): StatusDataV1
    {
        return new StatusDataV1(
            framework: 'Symfony',
            type: 'Microservice',
            status: 'active',
        );
    }
}
