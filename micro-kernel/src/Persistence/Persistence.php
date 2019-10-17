<?php

namespace DNA\MicroKernel\Persistence;

interface Persistence
{
    public function generateId(): int;

    public function persist(array $data): void;

    public function retrieve(array $data): ?array;
}
