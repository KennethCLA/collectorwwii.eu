<?php

namespace App\Policies;

use App\Models\Newspaper;
use App\Models\User;

class NewspaperPolicy extends AdminOnlyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Newspaper $newspaper): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->deny();
    }

    public function update(User $user, Newspaper $newspaper): bool
    {
        return $this->deny();
    }

    public function delete(User $user, Newspaper $newspaper): bool
    {
        return $this->deny();
    }
}
