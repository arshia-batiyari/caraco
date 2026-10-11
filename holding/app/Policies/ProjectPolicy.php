<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\EditsContent;

class ProjectPolicy
{
    use EditsContent;

    public function viewAny(User $user): bool
    {
        return $this->edits($user);
    }

    public function create(User $user): bool
    {
        return $this->edits($user);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->edits($user);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->edits($user);
    }
}
