<?php

namespace Yiisoft\Yii\Debug\Collector;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Yii\Debug\Target\TargetInterface;

class EventCollector implements CollectorInterface, EventDispatcherInterface
{
    private array $events = [];

    private TargetInterface $target;
    private EventDispatcherInterface $dispatcher;

    public function __construct(TargetInterface $target, EventDispatcherInterface $dispatcher)
    {
        $this->target = $target;
        $this->dispatcher = $dispatcher;
    }

    public function export(): void
    {
        $this->target->add($this->events);
    }

    public function setTarget(TargetInterface $target): void
    {
        $this->target = $target;
    }

    public function dispatch(object $event)
    {
        $this->collectEvent($event);

        return $this->dispatcher->dispatch($event);
    }

    private function collectEvent(object $event): void
    {
        $this->events[] = [
            'event' => $event,
            'time' => microtime(true),
        ];
    }
}
