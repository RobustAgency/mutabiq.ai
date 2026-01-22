<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use App\Events\UserCreated;
use Laravel\Cashier\Billable;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'supabase_id',
        'role',
        'password',
        'plan_id',
        'organization_id',
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

    protected $dispatchesEvents = [
        'created' => UserCreated::class,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    /**
     * Create a new user from Supabase data.
     *
     * @param  array  $attributes  User attributes including name, email, supabase_id, etc.
     * @return self The created user instance
     */
    public static function registerUser(array $attributes): self
    {
        $role = $attributes['role'];

        if ($role instanceof UserRole) {
            $roleValue = $role->value;
        } else {
            $roleValue = strtolower($role ?? '');
        }
        $user = self::create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'supabase_id' => $attributes['supabase_id'],
            'role' => UserRole::tryFrom($roleValue),
            'organization_id' => $attributes['organization_id'] ?? null,
        ]);

        // Dispatch Registered event
        event(new Registered($user));

        return $user;
    }

    /**
     * Get the organization that owns the user.
     *
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The projects that belong to the user.
     *
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withPivot('role');
    }
}
