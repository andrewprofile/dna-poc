<?php

declare(strict_types=1);

namespace DNA\Plugin\Persistence;

use DNA\MicroKernel\Persistence\Client;
use DNA\MicroKernel\Persistence\Persistence;

final class InMemoryPersistence implements Persistence
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function generateId(): int
    {
        return $this->client->lastInsertId();
    }

    public function persist(array $data): void
    {
        $this->client->query($data['key'], $data['value']);
    }

    public function retrieve(array $data): ?array
    {
        return $this->client->fetchAll($data['key'], $data['value']);
    }
}
