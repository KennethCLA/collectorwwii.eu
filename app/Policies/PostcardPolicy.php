<?php

namespace App\Policies;

use App\Models\Postcard;
use App\Models\User;

class PostcardPolicy extends AdminOnlyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Postcard $postcard): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->deny();
    }

    public function update(User $user, Postcard $postcard): bool
    {
        return $this->deny();
    }

    public function delete(User $user, Postcard $postcard): bool
    {
        return $this->deny();
    }
}
