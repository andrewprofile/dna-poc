<?php

declare(strict_types=1);

namespace DNA\Plugin\Manager;

class PluginManager
{
    /**
     * @var array|null
     */
    protected $defaultSettings;

    /**
     * @var array|null
     */
    protected $settings;

    public function __construct()
    {
        $this->getDefaultSettings();
    }

    public function getDefaultSettings(): array
    {
        if ($this->defaultSettings === null) {
            $this->defaultSettings = require __DIR__ . '/../../config/default.php';
        }

        return $this->defaultSettings;
    }

    public function getSettings(): array
    {
        if ($this->settings === null) {
            $this->refreshSettings();
        }

        return $this->settings;
    }

    public function refreshSettings(): void
    {
        $this->settings = $this->defaultSettings;
    }

    public function updateSettings(string $key, string $value): void
    {
        $this->settings[$key] = $value;
    }
}
