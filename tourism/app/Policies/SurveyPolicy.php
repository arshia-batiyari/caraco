<?php

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;
use App\Policies\Concerns\EditsContent;

class SurveyPolicy
{
    use EditsContent;

    public function viewAny(User $user): bool
    {
        return $this->edits($user);
    }

    public function view(User $user, Survey $survey): bool
    {
        return $this->edits($user);
    }

    public function create(User $user): bool
    {
        return $this->edits($user);
    }

    public function update(User $user, Survey $survey): bool
    {
        return $this->edits($user);
    }

    public function delete(User $user, Survey $survey): bool
    {
        return $user->isAdmin();
    }
}
