<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Status;

use App\Service\Status\StatusManager;
use App\Service\Status\StatusProviderInterface;
use App\Service\Status\V1\DTO\StatusDataV1;
use App\Service\Status\V2\DTO\StatusDataV2;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class StatusManagerTest
 * Изолированный модульный тест для проверки логики выбора версий StatusManager.
 */
final class StatusManagerTest extends TestCase
{
    /**
     * Тест проверяет успешное получение данных для зарегистрированных версий провайдеров.
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testGetStatusForVersionSuccess(): void
    {
        // 1. Создаем мок-провайдер для API V1
        $v1DtoData = $this->loadFixtureByName('status_v1');
        $v1Dto = StatusDataV1::fromArray($v1DtoData);
        $v1Provider = $this->createStub(StatusProviderInterface::class);
        $v1Provider->method('getVersion')->willReturn(1);
        $v1Provider->method('getData')->willReturn($v1Dto);

        // 2. Создаем мок-провайдер для API V2
        $v2DtoData = $this->loadFixtureByName('status_v2');
        $v2Dto = StatusDataV2::fromArray($v2DtoData);
        $v2Provider = $this->createStub(StatusProviderInterface::class);
        $v2Provider->method('getVersion')->willReturn(2);
        $v2Provider->method('getData')->willReturn($v2Dto);

        $providersCollection = [$v1Provider, $v2Provider];

        // 3. Инициализируем тестируемый объект (SUT - System Under Test)
        $manager = new StatusManager($providersCollection);

        // 4. Проверяем корректность маршрутизации запросов к провайдерам
        $this->assertSame($v1DtoData, $manager->getStatusForVersion(1));
        $this->assertSame($v2DtoData, $manager->getStatusForVersion(2));
    }

    /**
     * Тест проверяет, что менеджер выбрасывает исключение, если запрашиваемая версия отсутствует.
     */
    public function testGetStatusForVersionThrowsExceptionWhenVersionNotFound(): void
    {
        // Создаем менеджер с пустым пулом провайдеров
        $manager = new StatusManager([]);

        // Ожидаем исключение InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status provider for API v999999 is not registered.');

        // Вызываем код, который должен стриггерить исключение
        $manager->getStatusForVersion(999999);
    }

    /**
     * @return array<mixed, mixed>
     */
    private function loadFixtureByName(string $name): array
    {
        $filePath = __DIR__ . '/fixtures/' . $name . '.json';

        if (!file_exists($filePath)) {
            $this->fail("Файл фикстуры не найден: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            $this->fail("Не удалось прочитать файл фикстуры: {$filePath}");
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            $this->fail("Фикстура {$name}.json содержит невалидный JSON (ожидался массив)");
        }

        return $data;
    }
}
