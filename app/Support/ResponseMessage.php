<?php

namespace App\Support;

use ArrayAccess;
use BadMethodCallException;
use JsonSerializable;

class ResponseMessage implements ArrayAccess, JsonSerializable
{
    protected int $statusCode;
    protected string $template;
    protected array $defaults;
    protected array $argumentOrder;

    public static function make(
        int $statusCode,
        string $template,
        array $defaults = [],
        array $argumentOrder = []
    ): self {
        return new self($statusCode, $template, $defaults, $argumentOrder);
    }

    public function __construct(
        int $statusCode,
        string $template,
        array $defaults = [],
        array $argumentOrder = []
    ) {
        $this->statusCode = $statusCode;
        $this->template = $template;
        $this->defaults = $defaults;
        $this->argumentOrder = $argumentOrder;
    }

    public function __invoke(mixed ...$arguments): array
    {
        return $this->formatPayload($this->mergeArguments($arguments));
    }

    public function toArray(): array
    {
        return $this->formatPayload($this->defaults);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->toArray());
    }

    public function offsetGet(mixed $offset): mixed
    {
        $payload = $this->toArray();
        return $payload[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('ResponseMessage is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('ResponseMessage is immutable.');
    }

    protected function mergeArguments(array $arguments): array
    {
        $context = $this->defaults;

        foreach ($this->argumentOrder as $index => $placeholder) {
            if (array_key_exists($index, $arguments) && $arguments[$index] !== null) {
                $context[$placeholder] = (string) $arguments[$index];
            }
        }

        return $context;
    }

    protected function formatPayload(array $context): array
    {
        $message = strtr($this->template, $context);

        return [
            'status_code' => $this->statusCode,
            'message' => $message,
        ];
    }
}



