<?php

namespace App\Policies;

use App\User;
use App\automation;
use Illuminate\Auth\Access\HandlesAuthorization;

class AutomationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the automation.
     *
     * @param  \App\User  $user
     * @param  \App\automation  $automation
     * @return mixed
     */
    public function view(User $user)
    {
        return config('mailcare.auth') && config('mailcare.automations');
    }

    /**
     * Determine whether the user can create automations.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return config('mailcare.auth') && config('mailcare.automations');
    }

    /**
     * Determine whether the user can update the automation.
     *
     * @param  \App\User  $user
     * @param  \App\automation  $automation
     * @return mixed
     */
    public function update(User $user)
    {
        return config('mailcare.auth') && config('mailcare.automations');
    }

    /**
     * Determine whether the user can delete the automation.
     *
     * @param  \App\User  $user
     * @param  \App\automation  $automation
     * @return mixed
     */
    public function delete(User $user)
    {
        return config('mailcare.auth') && config('mailcare.automations');
    }

    /**
     * Determine whether the user can restore the automation.
     *
     * @param  \App\User  $user
     * @param  \App\automation  $automation
     * @return mixed
     */
    public function restore(User $user)
    {
        return config('mailcare.auth') && config('mailcare.automations');
    }

    /**
     * Determine whether the user can permanently delete the automation.
     *
     * @param  \App\User  $user
     * @param  \App\automation  $automation
     * @return mixed
     */
    public function forceDelete(User $user)
    {
        return config('mailcare.auth') && config('mailcare.automations');
    }
}
