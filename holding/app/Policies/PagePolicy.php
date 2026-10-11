<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use App\Policies\Concerns\EditsContent;

class PagePolicy
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

    public function update(User $user, Page $page): bool
    {
        return $this->edits($user);
    }

    public function delete(User $user, Page $page): bool
    {
        return $this->edits($user) && $page->key !== 'about';
    }
}
