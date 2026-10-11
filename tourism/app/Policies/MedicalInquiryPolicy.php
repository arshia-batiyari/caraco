<?php

namespace App\Policies;

use App\Models\MedicalInquiry;
use App\Models\User;
use App\Policies\Concerns\EditsContent;

class MedicalInquiryPolicy
{
    use EditsContent;

    public function viewAny(User $user): bool
    {
        return $this->edits($user);
    }

    public function view(User $user, MedicalInquiry $inquiry): bool
    {
        return $this->edits($user);
    }

    public function update(User $user, MedicalInquiry $inquiry): bool
    {
        return $this->edits($user);
    }

    public function delete(User $user, MedicalInquiry $inquiry): bool
    {
        return $user->isAdmin();
    }
}
