<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Month;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonthPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Month');
    }

    public function view(AuthUser $authUser, Month $month): bool
    {
        return $authUser->can('View:Month');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Month');
    }

    public function update(AuthUser $authUser, Month $month): bool
    {
        return $authUser->can('Update:Month');
    }

    public function delete(AuthUser $authUser, Month $month): bool
    {
        return $authUser->can('Delete:Month');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Month');
    }

    public function restore(AuthUser $authUser, Month $month): bool
    {
        return $authUser->can('Restore:Month');
    }

    public function forceDelete(AuthUser $authUser, Month $month): bool
    {
        return $authUser->can('ForceDelete:Month');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Month');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Month');
    }

    public function replicate(AuthUser $authUser, Month $month): bool
    {
        return $authUser->can('Replicate:Month');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Month');
    }

}