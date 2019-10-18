<?php

declare(strict_types=1);

namespace DNA\MicroKernel\Persistence;

interface Client
{
    public function getPrefix(): string;

    public function getTableName(): string;

    public function lastInsertId(): int;

    public function query(string $query, string $value): void;

    public function quote(string $value): string;

    public function fetchAll(string $query, string $value): ?array;

    public function fetchRow(string $query, string $value): ?array;
}
