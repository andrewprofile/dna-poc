<?php

declare(strict_types=1);

namespace DNA\Plugin\Controller;

use DNA\HttpClient\Client;
use DNA\HttpClient\Provider\Exception\ProviderException;
use DNA\HttpClient\Response\JsonResponse;
use DNA\Plugin\Installer\PluginInstaller;
use DNA\Plugin\Manager\PluginManager;
use DNA\Plugin\Manager\ProductPresentationManager;
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
     * @var PluginManager
     */
    private $pluginManager;

    /**
     * @var ProductPresentationManager
     */
    private $productPresentationManager;

    /**
     * @throws ProviderException
     */
    public function __construct()
    {
        $this->pluginManager = new PluginManager();
        $defaultSettings = $this->pluginManager->getDefaultSettings();
        $this->client = new Client(
            $defaultSettings['license_key'],
            $defaultSettings['api_version'],
            $defaultSettings['plugin_version']
        );
        $productRepository = new ProductRepository(new InMemoryPersistence(new InMemoryClient()));
        $presentationRepository = new PresentationRepository(new InMemoryPersistence(new InMemoryClient()));
        $this->productPresentationManager = new ProductPresentationManager(
            $this->client,
            $productRepository,
            $presentationRepository
        );
    }

    public function isNewestPluginVersionAction(): ResponseInterface
    {
        return new JsonResponse(
            [
            'current_version' => $this->client->getPluginVersion(),
            'version' => $this->client->getRemoteVersion(),
            'is_newest_version' => $this->client->isNewestVersion(),
            ]
        );
    }

    public function installAction(): ResponseInterface
    {
        $installer = new PluginInstaller($this->pluginManager);
        $result = $installer->install();

        return new JsonResponse(
            [
            'installed' => $result,
            ]
        );
    }

    public function updateAction(): ResponseInterface
    {
        $newVersion = $this->client->getRemoteVersion();
        $oldVersion = $this->client->getPluginVersion();

        $installer = new PluginInstaller($this->pluginManager);
        $installer->update($oldVersion, $newVersion);

        return new JsonResponse(
            [
            'old_version' => $oldVersion,
            'version' => $newVersion,
            ]
        );
    }

    public function uninstallAction(): ResponseInterface
    {
        $installer = new PluginInstaller($this->pluginManager);
        $result = $installer->uninstall();

        return new JsonResponse(
            [
            'uninstalled' => $result,
            ]
        );
    }

    public function getProductPresentation(string $sku): ResponseInterface
    {
        $result = $this->productPresentationManager->getMetadataOfProductPresentation($sku);

        return new JsonResponse(
            [
            'product_presentation' => $result,
            ],
            $result === null ? 400 : 200
        );
    }

    public function linkPresentationToProductAction(string $sku): ResponseInterface
    {
        $result = $this->productPresentationManager->linkPresentationToProduct($sku);

        return new JsonResponse(
            [
            'presentation_linked' => $result,
            ],
            $result === null ? 400 : 200
        );
    }

    public function synchronizationPresentationWithProductAction(string $sku): ResponseInterface
    {
        $result = $this->productPresentationManager->synchronizationPresentationWithProduct($sku);

        return new JsonResponse(
            [
            'product_presentation' => $result,
            ],
            $result === null ? 400 : 200
        );
    }
}
