<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper;

use CybertronianKelvin\Graper\Models\GraperPage;
use Illuminate\Database\Eloquent\Builder;

class Graper
{
    public function pages(): Builder
    {
        return GraperPage::query();
    }

    public function findPage(int|string $id): ?GraperPage
    {
        return GraperPage::find($id);
    }

    public function findPageBySlug(string $slug): ?GraperPage
    {
        return GraperPage::where('slug', $slug)->first();
    }
}
