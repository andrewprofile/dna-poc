<?php

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

    public function query($query, $value): void
    {
       $this->data[$query] = $this->quote($value);
    }

    public function quote($value): string
    {
        return (string) $value;
    }

    public function fetchAll($query, $value): array
    {
        return $this->fetchRow($query, $value);
    }

    public function fetchRow($query, $value): array
    {
        return (isset($this->data[$query]) && $this->data[$query] === $value) ? $this->data[$query] : null;
    }
}
