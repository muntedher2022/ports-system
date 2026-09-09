<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RevenueRecord;
use Illuminate\Auth\Access\HandlesAuthorization;

class RevenueRecordPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RevenueRecord');
    }

    public function view(AuthUser $authUser, RevenueRecord $revenueRecord): bool
    {
        return $authUser->can('View:RevenueRecord');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RevenueRecord');
    }

    public function update(AuthUser $authUser, RevenueRecord $revenueRecord): bool
    {
        return $authUser->can('Update:RevenueRecord');
    }

    public function delete(AuthUser $authUser, RevenueRecord $revenueRecord): bool
    {
        return $authUser->can('Delete:RevenueRecord');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RevenueRecord');
    }

    public function restore(AuthUser $authUser, RevenueRecord $revenueRecord): bool
    {
        return $authUser->can('Restore:RevenueRecord');
    }

    public function forceDelete(AuthUser $authUser, RevenueRecord $revenueRecord): bool
    {
        return $authUser->can('ForceDelete:RevenueRecord');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RevenueRecord');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RevenueRecord');
    }

    public function replicate(AuthUser $authUser, RevenueRecord $revenueRecord): bool
    {
        return $authUser->can('Replicate:RevenueRecord');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RevenueRecord');
    }

}