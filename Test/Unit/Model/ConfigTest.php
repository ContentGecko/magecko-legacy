<?php
declare(strict_types=1);

namespace Magecko\Blog\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magecko\Blog\Model\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testNormalizesConfiguredRoute(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn('/Magecko-Test/');

        $this->assertSame('magecko-test', (new Config($scopeConfig))->getRoute());
    }

    public function testFallsBackToBlogRoute(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturn('');

        $this->assertSame('blog', (new Config($scopeConfig))->getRoute());
    }

    public function testRejectsInvalidRouteShapes(): void
    {
        $this->assertFalse(Config::isValidRoute('two/levels'));
        $this->assertFalse(Config::isValidRoute('spaces are invalid'));
        $this->assertTrue(Config::isValidRoute('magecko-test'));
    }
}
