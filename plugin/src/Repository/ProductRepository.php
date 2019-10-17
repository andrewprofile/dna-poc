<?php

namespace DNA\Plugin\Repository;

use DNA\MicroKernel\Persistence\Persistence;

class ProductRepository
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
}
