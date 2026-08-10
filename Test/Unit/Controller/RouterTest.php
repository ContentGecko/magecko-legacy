<?php
declare(strict_types=1);

namespace Magecko\Blog\Test\Unit\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magecko\Blog\Controller\Router;
use Magecko\Blog\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private $actionFactory;
    private $config;

    protected function setUp(): void
    {
        $this->actionFactory = $this->createMock(ActionFactory::class);
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isStorefrontEnabled', 'getRoute'])
            ->getMock();
    }

    public function testDisabledStorefrontLeavesBlogRouteForMagefan(): void
    {
        $this->config->method('isStorefrontEnabled')->willReturn(false);
        $this->actionFactory->expects($this->never())->method('create');

        $request = $this->createRequestMock();
        $request->method('getModuleName')->willReturn('');

        $this->assertNull($this->createRouter()->match($request));
    }

    public function testCustomRouteLeavesBlogRouteForMageplaza(): void
    {
        $this->config->method('isStorefrontEnabled')->willReturn(true);
        $this->config->method('getRoute')->willReturn('magecko-test');
        $this->actionFactory->expects($this->never())->method('create');

        $request = $this->createRequestMock();
        $request->method('getModuleName')->willReturn('');
        $request->method('getPathInfo')->willReturn('/blog');

        $this->assertNull($this->createRouter()->match($request));
    }

    public function testConfiguredLandingRouteIsClaimed(): void
    {
        $this->config->method('isStorefrontEnabled')->willReturn(true);
        $this->config->method('getRoute')->willReturn('magecko-test');

        $action = $this->createMock(ActionInterface::class);
        $this->actionFactory->expects($this->once())
            ->method('create')
            ->with(Forward::class)
            ->willReturn($action);

        $request = $this->createRequestMock();
        $request->method('getModuleName')->willReturn('');
        $request->method('getPathInfo')->willReturn('/magecko-test');
        $request->method('setModuleName')->willReturnSelf();
        $request->method('setControllerName')->willReturnSelf();
        $request->method('setActionName')->willReturnSelf();
        $request->method('setParam')->willReturnSelf();
        $request->method('setAlias')->willReturnSelf();

        $this->assertSame($action, $this->createRouter()->match($request));
    }

    public function testInvalidConfiguredRouteFailsClosed(): void
    {
        $this->config->method('isStorefrontEnabled')->willReturn(true);
        $this->config->method('getRoute')->willReturn('invalid/nested-route');
        $this->actionFactory->expects($this->never())->method('create');

        $request = $this->createRequestMock();
        $request->method('getModuleName')->willReturn('');
        $request->method('getPathInfo')->willReturn('/invalid/nested-route');

        $this->assertNull($this->createRouter()->match($request));
    }

    private function createRouter(): Router
    {
        return new Router($this->actionFactory, $this->config);
    }

    /**
     * @return RequestInterface|MockObject
     */
    private function createRequestMock(): RequestInterface
    {
        return $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getModuleName',
                'getPathInfo',
                'setModuleName',
                'setControllerName',
                'setActionName',
                'setParam',
                'setAlias',
            ])
            ->getMock();
    }
}
