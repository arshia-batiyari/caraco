<?php

namespace App\Policies;

use App\Models\FeedbackSubmission;
use App\Models\User;
use App\Policies\Concerns\EditsContent;

class FeedbackPolicy
{
    use EditsContent;

    public function viewAny(User $user): bool
    {
        return $this->edits($user);
    }

    public function view(User $user, FeedbackSubmission $submission): bool
    {
        return $this->edits($user);
    }

    public function update(User $user, FeedbackSubmission $submission): bool
    {
        return $this->edits($user);
    }

    public function delete(User $user, FeedbackSubmission $submission): bool
    {
        return $user->isAdmin();
    }
}
