<?php

declare(strict_types=1);

namespace DNA\Plugin\Manager;

use DNA\HttpClient\Client;
use DNA\Plugin\Repository\PresentationRepository;
use DNA\Plugin\Repository\ProductRepository;

class ProductPresentationManager
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

    public function __construct(
        Client $client,
        ProductRepository $productRepository,
        PresentationRepository $presentationRepository
    ) {
        $this->client = $client;
        $this->productRepository = $productRepository;
        $this->presentationRepository = $presentationRepository;
    }

    public function linkPresentationToProduct(string $sku): ?array
    {
        $product = $this->productRepository->findBySku($sku);
        $presentation = $this->getPresentationFromSource($sku);

        if ($product === null || $presentation === null) {
            return null;
        }

        $productPresentation = [
            'productId' => $product['id'],
            'presentationId' => $presentation['id'],
            'presentationName' => $presentation['name'],
            'presentationContent' => $presentation['content'],
            'sku' => $sku,
        ];

        $this->presentationRepository->save($productPresentation);

        return $productPresentation;
    }

    public function synchronizationPresentationWithProduct(string $sku): ?array
    {
        $productPresentation = $this->getMetadataOfProductPresentation($sku);
        $presentationFromSource = $this->getPresentationFromSource($sku);

        if ($productPresentation === null || $presentationFromSource === null) {
            return null;
        }

        $presentation = [];

        foreach ($presentationFromSource as $key => $value) {
            foreach ($productPresentation as $metadata) {
                if ($key === $metadata) {
                    $presentation[$key] = $value;
                    unset($presentationFromSource[$key], $productPresentation[$metadata]);
                }
            }
        }

        return $presentation;
    }

    public function getMetadataOfProductPresentation(string $sku): ?array
    {
        $presentation = $this->presentationRepository->findBySku($sku);

        return !empty($presentation) ? $presentation : null;
    }

    protected function getPresentationFromSource(string $sku): ?array
    {
        $presentation = $this->client->post('presentation/'.$sku);

        return !empty($presentation) ? $presentation : null;
    }
}
