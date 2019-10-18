<?php

declare(strict_types=1);

namespace DNA\Plugin\Repository;

use DNA\MicroKernel\Persistence\Persistence;
use DNA\MicroKernel\Util\Util;

class PresentationRepository
{
    /**
     * @var Persistence
     */
    private $persistence;

    public function __construct(Persistence $persistence)
    {
        $this->persistence = $persistence;
    }

    public function findBySku(string $sku): ?array
    {
        return $this->persistence->retrieve(['key' => 'sku', 'value' => $sku]);
    }

    public function save(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->persistence->persist(
                [
                'key' => (new Util())->deCamelize($key),
                'value' => $value,
                ]
            );
        }
    }
}
