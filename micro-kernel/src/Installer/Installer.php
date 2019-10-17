<?php

namespace DNA\MicroKernel\Installer;

interface Installer
{
    public function install(): bool;

    public function uninstall(): bool;

    public function update(string $oldVersion, string $newVersion): void;
}
