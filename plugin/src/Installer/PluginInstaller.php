<?php

declare(strict_types=1);

namespace DNA\Plugin\Installer;

use DNA\MicroKernel\Installer\Installer;
use DNA\Plugin\Manager\PluginManager;

class PluginInstaller implements Installer
{
    /**
     * @var PluginManager
     */
    protected $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public function install(): bool
    {
        $this->pluginManager->updateSettings('installed', '1');

        return true;
    }

    public function uninstall(): bool
    {
        $this->pluginManager->updateSettings('installed', '0');

        return true;
    }

    public function update(string $oldVersion, string $newVersion): void
    {
        $this->pluginManager->updateSettings('plugin_version', $newVersion);
    }
}
