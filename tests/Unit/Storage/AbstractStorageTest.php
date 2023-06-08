<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Debug\Tests\Unit\Storage;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Debug\Collector\CollectorInterface;
use Yiisoft\Yii\Debug\DebuggerIdGenerator;
use Yiisoft\Yii\Debug\Storage\MemoryStorage;
use Yiisoft\Yii\Debug\Storage\StorageInterface;

abstract class AbstractStorageTest extends TestCase
{
    /**
     * @dataProvider dataProvider()
     */
    public function testAddAndGet(array $data): void
    {
        $idGenerator = new DebuggerIdGenerator();
        $storage = $this->getStorage($idGenerator);
        $collector = $this->createFakeCollector($data);

        $this->assertEquals([], $storage->getData());
        $storage->addCollector($collector);
        $this->assertEquals([$collector->getName() => $data], $storage->getData());
    }

    /**
     * @dataProvider dataProvider()
     */
    public function testRead(array $data): void
    {
        $idGenerator = new DebuggerIdGenerator();
        $storage = $this->getStorage($idGenerator);
        $collector = $this->createFakeCollector($data);

        $storage->addCollector($collector);
        $expectedData = $storage->getData();
        if (!$storage instanceof MemoryStorage) {
            $storage->flush();
        }
        $data = $storage->read(StorageInterface::TYPE_DATA);
        $this->assertEquals([$idGenerator->getId() => $expectedData], $data);
    }

    /**
     * @dataProvider dataProvider()
     */
    public function testFlush(array $data): void
    {
        $idGenerator = new DebuggerIdGenerator();
        $storage = $this->getStorage($idGenerator);
        $collector = $this->createFakeCollector($data);

        $storage->addCollector($collector);
        $storage->flush();
        $this->assertEquals([], $storage->getData());
    }

    abstract public function getStorage(DebuggerIdGenerator $idGenerator): StorageInterface;

    public function dataProvider(): array
    {
        return [
            [[1, 2, 3]],
            [['string']],
            [[[['', 0, false]]]],
            [['test']],
            [[false]],
            [[null]],
            [[0]],
        ];
    }

    protected function createFakeCollector(array $data)
    {
        $collector = $this->getMockBuilder(CollectorInterface::class)->getMock();
        $collector
            ->method('getCollected')
            ->willReturn($data);
        $collector
            ->method('getName')
            ->willReturn('Mock_Collector');

        return $collector;
    }
}