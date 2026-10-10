<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\EditsContent;
use Illuminate\Database\Eloquent\Model;

class CatalogPolicy
{
    use EditsContent;

    public function viewAny(User $user): bool
    {
        return $this->edits($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->edits($user);
    }

    public function create(User $user): bool
    {
        return $this->edits($user);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->edits($user);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->edits($user);
    }
}
