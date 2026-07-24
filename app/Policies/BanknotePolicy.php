<?php

namespace App\Policies;

use App\Models\Banknote;
use App\Models\User;

class BanknotePolicy extends AdminOnlyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Banknote $banknote): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->deny();
    }

    public function update(User $user, Banknote $banknote): bool
    {
        return $this->deny();
    }

    public function delete(User $user, Banknote $banknote): bool
    {
        return $this->deny();
    }
}
