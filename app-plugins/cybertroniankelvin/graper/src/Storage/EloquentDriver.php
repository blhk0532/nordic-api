<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Storage;

use CybertronianKelvin\Graper\Models\GraperPage;

class EloquentDriver implements StorageDriver
{
    public function load(int|string $id): array
    {
        $page = GraperPage::findOrFail($id);

        return [
            'html' => $page->html ?? '',
            'css' => $page->css ?? '',
            'project_data' => $page->project_data ?? [],
        ];
    }

    public function save(int|string $id, array $data): void
    {
        $page = GraperPage::findOrFail($id);

        $page->update([
            'html' => $data['html'] ?? $page->getAttributeValue('html'),
            'css' => $data['css'] ?? $page->getAttributeValue('css'),
            'project_data' => $data['project_data'] ?? $page->getAttributeValue('project_data'),
        ]);
    }
}
