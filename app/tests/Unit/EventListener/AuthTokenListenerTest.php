<?php

namespace App\Tests\Unit\EventListener;

use App\EventListener\AuthTokenListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthTokenListenerTest extends TestCase
{
    private const TEST_SECRET = 'valid-test-token-123';

    /**
     * Тест: запрос проходит, если токен верный
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testOnKernelRequestWithValidToken(): void
    {
        // 1. Создаем слушатель и передаем тестовый секрет прямо в конструктор
        $listener = new AuthTokenListener(self::TEST_SECRET);

        // 2. Создаем пустой запрос, добавляем в него правильный заголовок и нужный роут
        $request = new Request();
        $request->headers->set(AuthTokenListener::HEADER_NAME, self::TEST_SECRET);
        $request->attributes->set('_route', 'api_v1_external_posts'); // Эмулируем конкретный маршрут

        // 3. Создаем событие Symfony Kernel (мокируем ядро)
        $event = $this->createRequestEvent($request);

        // 4. Запускаем метод
        $listener->onKernelRequest($event);

        // 5. Явная проверка: успешный запрос не должен вызывать создание Response внутри слушателя
        self::assertNull($event->getResponse(), 'При валидном токене слушатель не должен прерывать запрос и создавать ответ.');
    }

    /**
     * Тест: выбрасывается исключение, если токен неверный или отсутствует
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testOnKernelRequestWithInvalidToken(): void
    {
        $listener = new AuthTokenListener(self::TEST_SECRET);

        $request = new Request();
        $request->headers->set(AuthTokenListener::HEADER_NAME, 'wrong-token');
        $request->attributes->set('_route', 'api_v1_external_posts');

        $event = $this->createRequestEvent($request);

        // Ожидаем, что Symfony выбросит 403 ошибку
        $this->expectException(AccessDeniedHttpException::class);

        $listener->onKernelRequest($event);
    }

    /**
     * Тест: токен не проверяется, если роут находится в белом списке.
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testOnKernelRequestSkipsWhitelistedRoute(): void
    {
        $listener = new AuthTokenListener(self::TEST_SECRET, ['api_v1_auth_login']);

        $request = new Request();
        // Эмулируем, что запрос пришел на эндпоинт логина
        $request->attributes->set('_route', 'api_v1_auth_login');
        // Токен намеренно не передаем

        $event = $this->createRequestEvent($request);

        // Метод должен завершиться без исключения, так как роут в белом списке
        $this->expectNotToPerformAssertions();
        $listener->onKernelRequest($event);
    }

    /**
     * Тест: слушатель игнорирует подзапросы (Sub Requests).
     * Безопасность должна проверяться только на главном запросе не загружая проверками остальные
     *
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testOnKernelRequestSkipsSubRequest(): void
    {
        $listener = new AuthTokenListener(self::TEST_SECRET);
        $request = new Request(); // Без токена

        // Создаем событие для SUB_REQUEST (например, внутренний рендеринг фрагмента)
        $event = new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::SUB_REQUEST
        );

        // Исключение быть не должно
        $this->expectNotToPerformAssertions();
        $listener->onKernelRequest($event);
    }

    /**
     * Хелпер для создания объекта события RequestEvent
     *
     * @param Request $request
     * @return RequestEvent
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function createRequestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );
    }
}
