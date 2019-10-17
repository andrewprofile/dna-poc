<?php

namespace DNA\Plugin\Manager;

use DNA\HttpClient\Client;
use DNA\Plugin\Repository\PresentationRepository;
use DNA\Plugin\Repository\ProductRepository;

class PresentationManager
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var PresentationRepository
     */
    private $presentationRepository;

    public function __construct($client, $productRepository, $presentationRepository)
    {
        $this->client = $client;
        $this->productRepository = $productRepository;
        $this->presentationRepository = $presentationRepository;
    }

    public function linkPresentationToProduct(string $sku, ?array $presentation): bool
    {
        $product = $this->productRepository->findBySku($sku);

        if ($product === null || $presentation === null) {
            return false;
        }

        $productPresentation = [
            'productId' => $product['id'],
            'presentationId' => $presentation['id'],
            'presentationName' => $presentation['name'],
            'presentationContent' => $presentation['content'],
            'sku' => $sku,
        ];

        $this->presentationRepository->save($productPresentation);

        return true;
    }

    public function getMetadataOfPresentation(string $sku): ?array
    {
        $presentation = $this->presentationRepository->findBySku($sku);

        return !empty($presentation) ? $presentation : null;
    }

    public function getPresentationFromSource(string $sku): ?array
    {
        $presentation = $this->client->post('presentation/'.$sku);
        return !empty($presentation) ? $presentation : null;
    }
}
