<?php

namespace Eightbitsnl\PolicyAndPermissionGenerator\Traits;

use App\User;
use Illuminate\Support\Str;

trait Permissions
{
	protected function checkIfUserHasPermission(?User $user, $ability, $model)
	{
		if( Str::startsWith($ability, 'attach') || Str::startsWith($ability, 'detach') )
			return null;

		$permission = class_basename($model) . '.'. $ability;
        return $user->can( $permission ) ? null : false;
	}

    public function before(?User $user, $ability, $model)
    {
		return $this->checkIfUserHasPermission($user, $ability, $model);
    }

    /**
     * Determine whether the user can view any
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function view(User $user, $model)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function update(User $user, $model)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function delete(User $user, $model)
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function restore(User $user, $model)
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\User  $model
     * @return mixed
     */
    public function forceDelete(User $user, $model)
    {
        return true;
    }
    
}
