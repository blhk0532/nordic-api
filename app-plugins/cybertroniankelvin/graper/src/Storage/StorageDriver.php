<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Storage;

interface StorageDriver
{
    public function load(int|string $id): array;

    public function save(int|string $id, array $data): void;
}
