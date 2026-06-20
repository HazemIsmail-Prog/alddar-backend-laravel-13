<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
// use Illuminate\Database\Eloquent\Attributes\Appends;
// use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
// use Illuminate\Database\Eloquent\Attributes\UnGuarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// #[UnGuarded]
// #[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
// #[Appends(['can', 'name'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $appends = ['can_update', 'can_delete', 'name'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_technician' => 'boolean',
        ];
    }

    public function getCanAttribute(): array
    {
        $permissions = $this->permissions->pluck('value')->toArray();
        $roles = $this->roles->load('permissions')->pluck('permissions.*.value')->flatten()->toArray();

        // return unique permissions and roles
        return array_unique(array_merge($permissions, $roles));
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->can);
    }

    public function getNameAttribute(): object
    {
        return (object) [
            'ar' => $this->name_ar,
            'en' => $this->name_en,
        ];
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'technician_id');
    }

    public function getCanUpdateAttribute()
    {
        return request()->user()->hasPermission('users_update');
    }

    public function getCanDeleteAttribute()
    {
        return request()->user()->hasPermission('users_delete');
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouse');
    }
}
