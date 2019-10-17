<?php

namespace DNA\Plugin\Controller;

use DNA\HttpClient\Client;
use DNA\HttpClient\Provider\Exception\ProviderException;
use DNA\HttpClient\Response\JsonResponse;
use DNA\Plugin\Installer\PluginInstaller;
use DNA\Plugin\Manager\PluginManager;
use DNA\Plugin\Manager\PresentationManager;
use DNA\Plugin\Persistence\InMemoryClient;
use DNA\Plugin\Persistence\InMemoryPersistence;
use DNA\Plugin\Repository\PresentationRepository;
use DNA\Plugin\Repository\ProductRepository;
use Psr\Http\Message\ResponseInterface;

final class PluginController
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var PresentationManager
     */
    private $presentationManager;

    /**
     * @throws ProviderException
     */
    public function __construct()
    {
        $defaultSettings = PluginManager::getDefaultSettings();
        $this->client = new Client(
            $defaultSettings['license_key'],
            $defaultSettings['api_version'],
            $defaultSettings['plugin_version']
        );
        $productRepository = new ProductRepository(new InMemoryPersistence(new InMemoryClient()));
        $presentationRepository = new PresentationRepository(new InMemoryPersistence(new InMemoryClient()));
        $this->presentationManager = new PresentationManager($this->client, $productRepository, $presentationRepository);
    }

    public function isNewestPluginVersionAction(): ResponseInterface
    {
        return new JsonResponse([
            'current_version' => $this->client->getPluginVersion(),
            'version' => $this->client->getRemoteVersion(),
            'is_newest_version' => $this->client->isNewestVersion(),
        ]);
    }

    public function installAction(): ResponseInterface
    {
        $installer = new PluginInstaller();
        $result = $installer->install();

        return new JsonResponse([
            'installed' => $result,
        ]);
    }

    public function updateAction(): ResponseInterface
    {
        $newVersion = $this->client->getRemoteVersion();
        $oldVersion = $this->client->getPluginVersion();

        $installer = new PluginInstaller();
        $installer->update($oldVersion, $newVersion);

        return new JsonResponse([
            'old_version' => $oldVersion,
            'version' => $newVersion,
        ]);
    }

    public function uninstallAction(): ResponseInterface
    {
        $installer = new PluginInstaller();
        $result = $installer->uninstall();

        return new JsonResponse([
            'uninstalled' => $result,
        ]);
    }

    public function getProductPresentation(string $sku): ResponseInterface
    {
        $result = $this->presentationManager->getPresentationFromSource($sku);

        return new JsonResponse([
            'product_presentation' => $result,
        ],
            $result === null ? 400 : 200
        );
    }

    public function linkPresentationToProductAction(string $sku): ResponseInterface
    {
        $presentation = $this->presentationManager->getPresentationFromSource($sku);
        $result = $this->presentationManager->linkPresentationToProduct(
            $sku,
            $presentation
        );

        return new JsonResponse([
            'presentation_linked' => $result,
        ],
            !$result ? 400 : 200
        );
    }

    public function synchronizationPresentationWithProductAction(string $sku): ResponseInterface
    {
        $presentationMetadata = $this->presentationManager->getMetadataOfPresentation($sku);
        $presentationFromSource = $this->presentationManager->getPresentationFromSource($sku);

        if ($presentationMetadata === null || $presentationFromSource === null){
            return new JsonResponse([
                'product_presentation' => [],
            ],
                400
            );
        }

        $presentation = [];

        foreach ($presentationFromSource as $key => $value) {
            foreach ($presentationMetadata as $metadata) {
                if ($key === $metadata) {
                    $presentation[$key] = $value;
                    unset($presentationFromSource[$key], $presentationMetadata[$metadata]);
                }
            }
        }

        return new JsonResponse([
            'product_presentation' => $presentation,
        ]);
    }
}
