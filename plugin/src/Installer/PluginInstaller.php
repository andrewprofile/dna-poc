<?php

namespace DNA\Plugin\Installer;

use DNA\MicroKernel\Installer\Installer;
use DNA\Plugin\Manager\PluginManager;

class PluginInstaller implements Installer
{
    public function install(): bool
    {
        PluginManager::updateSettings('installed', '1');
        return true;
    }

    public function uninstall(): bool
    {
        PluginManager::updateSettings('installed', '0');
        return true;
    }

    public function update(string $oldVersion, string $newVersion): void
    {
        PluginManager::updateSettings('plugin_version', $newVersion);
    }
}
