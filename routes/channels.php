<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{user_id}', function(User $user, int $user_id) {
    return (int) $user->id === (int) $user_id;
});
