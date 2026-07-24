<?php

namespace App\Policies;

use App\Models\MapLocation;
use App\Models\User;

class MapLocationPolicy extends AdminOnlyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, MapLocation $mapLocation): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->deny();
    }

    public function update(User $user, MapLocation $mapLocation): bool
    {
        return $this->deny();
    }

    public function delete(User $user, MapLocation $mapLocation): bool
    {
        return $this->deny();
    }
}
