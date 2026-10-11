<?php

namespace App\Policies;

use App\Models\Inquiry;
use App\Models\User;
use App\Policies\Concerns\EditsContent;

class InquiryPolicy
{
    use EditsContent;

    public function viewAny(User $user): bool
    {
        return $this->edits($user);
    }

    public function view(User $user, Inquiry $inquiry): bool
    {
        return $this->edits($user);
    }
}
