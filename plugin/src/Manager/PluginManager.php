<?php

namespace DNA\Plugin\Manager;

class PluginManager
{
    /**
     * @var array
     */
    protected static $defaultSettings;

    /**
     * @var array
     */
    protected static $settings;

    public static function getDefaultSettings(): array
    {
        if (self::$defaultSettings === null) {
            self::$defaultSettings = require __DIR__.'/../../config/default.php';
        }

        return self::$defaultSettings;
    }

    public static function getSettings(bool $needsRefresh = false): array
    {
        if (self::$settings === null || $needsRefresh) {
            self::$settings = self::$defaultSettings;
        }

        return self::$settings;
    }

    public static function updateSettings(string $key, string $value): void
    {
        self::$settings[$key] = $value;
    }
}
