<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait EditsContent
{
    protected function edits(User $user): bool
    {
        return in_array($user->role, ['admin', 'editor'], true);
    }
}
