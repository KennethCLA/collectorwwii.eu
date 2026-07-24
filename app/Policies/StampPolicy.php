<?php

namespace App\Policies;

use App\Models\Stamp;
use App\Models\User;

class StampPolicy extends AdminOnlyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Stamp $stamp): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->deny();
    }

    public function update(User $user, Stamp $stamp): bool
    {
        return $this->deny();
    }

    public function delete(User $user, Stamp $stamp): bool
    {
        return $this->deny();
    }
}
