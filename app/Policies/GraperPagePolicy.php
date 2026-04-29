<?php

namespace App\Policies;

use App\Models\User;
use CybertronianKelvin\Graper\Models\GraperPage;

class GraperPagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GraperPage $graperPage): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GraperPage $graperPage): bool
    {
        return true;
    }

    public function delete(User $user, GraperPage $graperPage): bool
    {
        return true;
    }
}
