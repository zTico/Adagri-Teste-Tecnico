<?php

namespace App\Policies;

use App\Models\RuralProducer;
use App\Models\User;

class RuralProducerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isViewer();
    }

    public function view(User $user, RuralProducer $ruralProducer): bool
    {
        return $user->isAdmin() || $user->isViewer();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, RuralProducer $ruralProducer): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, RuralProducer $ruralProducer): bool
    {
        return $user->isAdmin();
    }
}
