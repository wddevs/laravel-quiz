<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AssetPolicy
{
    public function view(User $user, Asset $asset): bool
    {
        return $asset->user_id === $user->id;
    }

    public function delete(User $user, Asset $asset)
    {
        return $asset->user_id === $user->id
            ? Response::allow()
            : Response::deny('You do not own this file.');
    }

    // за потреби: create, update, viewAny, restore, forceDelete …
}
