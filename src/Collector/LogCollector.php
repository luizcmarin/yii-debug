<?php

namespace Yiisoft\Yii\Debug\Collector;

final class LogCollector implements CollectorInterface
{
    use CollectorTrait;

    private array $messages = [];

    public function collect(): array
    {
        return $this->messages;
    }

    public function dispatch(...$payload): void
    {
        if (count($payload) !== 3) {
            throw new \InvalidArgumentException('$payload should contain $level, $message and $context variables');
        }
        [$level, $message, $context] = $payload;

        $this->messages[] = [
            'time' => microtime(true),
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }
}
