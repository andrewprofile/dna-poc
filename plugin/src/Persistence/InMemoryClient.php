<?php

declare(strict_types=1);

namespace DNA\Plugin\Persistence;

final class InMemoryClient
{
    private $data = [];

    private $lastId = 0;

    public function getPrefix(): string
    {
        return '';
    }

    public function getTableName(): string
    {
        return "{$this->getPrefix()}in_memory";
    }

    public function lastInsertId(): int
    {
        $this->lastId++;

        return $this->lastId;
    }

    public function query(string $query, string $value): void
    {
        $this->data[$query] = $this->quote($value);
    }

    public function quote(string $value): string
    {
        return (string) $value;
    }

    public function fetchAll(string $query, string $value): array
    {
        return $this->fetchRow($query, $value);
    }

    public function fetchRow(string $query, string $value): ?array
    {
        return (isset($this->data[$query]) && $this->data[$query] === $value) ? [$this->data[$query]] : null;
    }
}
