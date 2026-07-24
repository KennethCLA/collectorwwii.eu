<?php

namespace App\Policies;

use App\Models\Magazine;
use App\Models\User;

class MagazinePolicy extends AdminOnlyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Magazine $magazine): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->deny();
    }

    public function update(User $user, Magazine $magazine): bool
    {
        return $this->deny();
    }

    public function delete(User $user, Magazine $magazine): bool
    {
        return $this->deny();
    }
}
