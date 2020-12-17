<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Debug\Tests\Collector;

use Yiisoft\Di\Container;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Router\RouteCollectorInterface;
use Yiisoft\Router\UrlMatcherInterface;
use Yiisoft\Yii\Debug\Collector\CollectorInterface;
use Yiisoft\Yii\Debug\Collector\RouterCollector;

final class RouterCollectorTest extends CollectorTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Yiisoft\Router\RouteCollectorInterface
     */
    private $routeCollector;

    private ?Container $container = null;

    /**
     * @param \Yiisoft\Yii\Debug\Collector\CollectorInterface|\Yiisoft\Yii\Debug\Collector\RouterCollector $collector
     */
    protected function collectTestData(CollectorInterface $collector): void
    {
        $routes = $this->createRoutes();
        $this->routeCollector
            ->method('getItems')
            ->willReturn($routes);
    }

    protected function getCollector(): CollectorInterface
    {
        $this->routeCollector = $this->createMock(RouteCollectorInterface::class);
        $routeCollector = Group::create();
        $routeCollector->addGroup(Group::create(null, $this->createRoutes()));

        $this->container = new Container(
            [
                UrlMatcherInterface::class => $this->routeCollector,
                RouteCollectionInterface::class => RouteCollection::class,
                RouteCollectorInterface::class => $routeCollector,
            ]
        );

        return new RouterCollector($this->container);
    }

    protected function checkCollectedData(CollectorInterface $collector): void
    {
        parent::checkCollectedData($collector);
        $this->assertArrayHasKey('routes', $collector->getCollected());
        $this->assertArrayHasKey('routesTree', $collector->getCollected());
        $this->assertEquals(
            $collector->getCollected()['routes'],
            $this->container->get(RouteCollectionInterface::class)->getRoutes()
        );
        $this->assertEquals(
            $collector->getCollected()['routesTree'],
            $this->container->get(RouteCollectionInterface::class)->getRouteTree()
        );
    }

    private function createRoutes(): array
    {
        return [
            Route::get('/'),
            Group::create(
                '/api',
                [
                    Route::get('/v1'),
                ]
            ),
        ];
    }
}
