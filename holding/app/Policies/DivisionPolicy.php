<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\User;
use App\Policies\Concerns\EditsContent;

class DivisionPolicy
{
    use EditsContent;

    public function viewAny(User $user): bool
    {
        return $this->edits($user);
    }

    public function update(User $user, Division $division): bool
    {
        return $this->edits($user);
    }
}
