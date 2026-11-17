<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Permissions\PanelPermissions;
use App\Enums\Permissions\WildcardPermissions;
use App\Enums\UserRole;
use App\Traits\HasBlameable;
use App\Traits\HasIsActiveScope;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Concerns\HasActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property bool $is_active
 */
class User extends Authenticatable implements FilamentUser
{
    use HasActivity;
    use HasBlameable;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasIsActiveScope;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $user): bool {
            if ($user->isLastDeveloper()) {
                return false;
            }

            if (Auth::id() !== null && Auth::id() === $user->id) {
                return false;
            }

            return true;
        });
    }

    public function canAccessPanel(?Panel $panel): bool
    {
        if (! $this->is_active || $panel === null) {
            return false;
        }

        $panelPermission = PanelPermissions::fromPanel($panel);
        $permission = $panelPermission !== null
            ? $panelPermission->value
            : "panels.view.{$panel->getId()}";

        return $this->can($permission);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'username', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::Developer->value)
            || $this->permissions()->where('name', WildcardPermissions::All->value)->exists()
            || $this->roles()->whereHas('permissions', fn ($query) => $query->where('name', WildcardPermissions::All->value))->exists();
    }

    public function canAssignRole(Role $role): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($role->name === UserRole::Developer->value) {
            return false;
        }

        $role->loadMissing('permissions');

        foreach ($role->permissions as $permission) {
            if ($permission->name === WildcardPermissions::All->value) {
                return false;
            }

            if ($role->name === UserRole::default()->value) {
                continue;
            }

            if (! $this->can($permission->name)) {
                return false;
            }
        }

        return true;
    }

    public function canManageUser(self $target): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($target->isSuperAdmin()) {
            return false;
        }

        $targetRoles = $target->roles()->with('permissions')->get();

        return $targetRoles->every(
            fn (\Illuminate\Database\Eloquent\Model $role): bool => $role instanceof Role && $this->canAssignRole($role)
        );
    }

    public function isLastDeveloper(): bool
    {
        if (! $this->hasRole(UserRole::Developer->value)) {
            return false;
        }

        return self::role(UserRole::Developer->value)->count() <= 1;
    }
}
