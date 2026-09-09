<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RevenueCenter;
use Illuminate\Auth\Access\HandlesAuthorization;

class RevenueCenterPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RevenueCenter');
    }

    public function view(AuthUser $authUser, RevenueCenter $revenueCenter): bool
    {
        return $authUser->can('View:RevenueCenter');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RevenueCenter');
    }

    public function update(AuthUser $authUser, RevenueCenter $revenueCenter): bool
    {
        return $authUser->can('Update:RevenueCenter');
    }

    public function delete(AuthUser $authUser, RevenueCenter $revenueCenter): bool
    {
        return $authUser->can('Delete:RevenueCenter');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RevenueCenter');
    }

    public function restore(AuthUser $authUser, RevenueCenter $revenueCenter): bool
    {
        return $authUser->can('Restore:RevenueCenter');
    }

    public function forceDelete(AuthUser $authUser, RevenueCenter $revenueCenter): bool
    {
        return $authUser->can('ForceDelete:RevenueCenter');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RevenueCenter');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RevenueCenter');
    }

    public function replicate(AuthUser $authUser, RevenueCenter $revenueCenter): bool
    {
        return $authUser->can('Replicate:RevenueCenter');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RevenueCenter');
    }

}