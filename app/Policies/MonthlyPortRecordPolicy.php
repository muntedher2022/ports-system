<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MonthlyPortRecord;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonthlyPortRecordPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MonthlyPortRecord');
    }

    public function view(AuthUser $authUser, MonthlyPortRecord $monthlyPortRecord): bool
    {
        return $authUser->can('View:MonthlyPortRecord');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MonthlyPortRecord');
    }

    public function update(AuthUser $authUser, MonthlyPortRecord $monthlyPortRecord): bool
    {
        return $authUser->can('Update:MonthlyPortRecord');
    }

    public function delete(AuthUser $authUser, MonthlyPortRecord $monthlyPortRecord): bool
    {
        return $authUser->can('Delete:MonthlyPortRecord');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MonthlyPortRecord');
    }

    public function restore(AuthUser $authUser, MonthlyPortRecord $monthlyPortRecord): bool
    {
        return $authUser->can('Restore:MonthlyPortRecord');
    }

    public function forceDelete(AuthUser $authUser, MonthlyPortRecord $monthlyPortRecord): bool
    {
        return $authUser->can('ForceDelete:MonthlyPortRecord');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MonthlyPortRecord');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MonthlyPortRecord');
    }

    public function replicate(AuthUser $authUser, MonthlyPortRecord $monthlyPortRecord): bool
    {
        return $authUser->can('Replicate:MonthlyPortRecord');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MonthlyPortRecord');
    }

}