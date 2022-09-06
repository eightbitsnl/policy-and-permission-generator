<!-- @format -->

# Generates Permissions and Policies

## :warning: Not production ready

This has not been tested in production (yet).

## Permissions

### Artisan command

```bash
php artisan permissions:generate
```

```bash
php artisan permissions:generate --model="App\Models\Article
```

This command will scan the `/app/` or `/app/Models` directory for Models. For each model, it will `firstOrCreate` permissions (using [spatie/laravel-permission](https://github.com/spatie/laravel-permission/)) for the following abilities:

```
create
view
update
delete
viewAny
restore
forceDelete
```

These Permissions will be named as : `{ModelName}.{ability}`, so for example if you have an `Article` model:

```
Article.create
Article.view
Article.update
Article.delete
Article.viewAny
Article.restore
Article.forceDelete
```

## Policies

### Artisan command

```bash
php artisan policies:generate
```

This command will scan the `/app/` directory for Models. For each model, it will publish a new `/app/Policies/{ModelName}Policy.php`, for example:

```php
<?php

namespace App\Policies;

use Eightbitsnl\PolicyAndPermissionGenerator\Traits\Permissions;
use App\Article;

use Illuminate\Auth\Access\HandlesAuthorization;

class ArticlePolicy
{
    use HandlesAuthorization,
        Permissions;
}

```

See: [Laravel Docs](https://laravel.com/docs/8.x/authorization#policy-methods) for more info on Policies.

### Trait

Policies use the `Eightbitsnl\PolicyAndPermissionGenerator\Traits\Permissions` trait.

**Example:** Say, we're checking if a user can `update` an `Article`.

1. `Permissions` trait adds `before()` method to each generated `Policy`.
   The before method will be executed before any other methods on the policy. More info [Laravel Docs](https://laravel.com/docs/8.x/authorization#policy-filters).
   This `before()` method checks if the user has a Permission, for example `Article.update`.
   - If the user **DOES NOT** have the Permission, it will return `false`
   - If the user **DOES** have the Permission, it will return `null`
1. When the `before()` method returns `null`, the Policy method is checked next, for example `Article@update(User $user, Article $model)`.
   This method returns `true` by default.

   ```php
   public function update(User $user, Article $model)
   {
   	   return true;
   }
   ```

   But, you can use this method to further write your app logic.

   ```php
   public function update(User $user, Article $model)
   {
   	   // user can only update their own articles
   	   return $user->id == $model->user_id;
   }
   ```

#### Free pro-tip regarding Super-Admins

When you have super-admin, you might want to allow this user to `update` all `Articles`, even the ones created by other users:

Then add a `Gate::after` to your `AuthServiceProvider`

```php
class AuthServiceProvider extends ServiceProvider
{
    // ...

    public function boot()
    {
        $this->registerPolicies();

        // ...

        Gate::after(function ($user, $ability) {
            return $user->hasRole('system-admin'); // write your own logic here, for example
        });
    }
}


```

So now we can return `null` in our Policy methods instead of `false`, so we fall trough to the `Gate::after` check.

```php
public function update(User $user, Article $model)
{
	// user can only update their own articles
	// return null, to fall trough
	return ($user->id == $model->user_id) ?: null;
}
```

##### TL;DR:

1. The `before()` trait method checks if the user has a Permission. This returns `false` or `null`. When `null` is returned:
1. The `Article@update(User $user, Article $model)` can do additional checks, and returns `true`, `false`, or `null`. When `null` is returned:
1. The `Gate::after` checks if the user is a super-admin, and returns `true` or `false`.

##### More info:

- [Defining a Super-Admin](https://spatie.be/docs/laravel-permission/v3/basic-usage/super-admin) for [spatie/laravel-permission](https://github.com/spatie/laravel-permission/)
- [When to use Gate::after in Laravel](https://freek.dev/1325-when-to-use-gateafter-in-laravel) by freek.dev
