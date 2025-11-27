<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
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

    /**
     * Get the posts authored by this user.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the comments made by this user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the social identities linked to this user.
     */
    public function socialIdentities(): HasMany
    {
        return $this->hasMany(SocialIdentity::class);
    }

    /**
     * Get the activity logs for this user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include admin users.
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->role('admin');
    }

    /**
     * Scope a query to only include regular users.
     */
    public function scopeRegularUsers(Builder $query): Builder
    {
        return $query->role('user');
    }

    /**
     * Get published posts count.
     */
    public function getPublishedPostsCountAttribute(): int
    {
        return $this->posts()->published()->count();
    }

    /**
     * Get the avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        // Return Gravatar as default
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }

    /**
     * Find or create user from social provider data.
     */
    public static function findOrCreateFromSocialite(object $socialUser, string $provider): self
    {
        // First, try to find user by social identity
        $socialIdentity = SocialIdentity::where('provider_name', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialIdentity) {
            return $socialIdentity->user;
        }

        // Then, try to find user by email
        $user = static::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            // Create new user
            $user = static::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(str()->random(24)),
                'avatar' => $socialUser->getAvatar(),
                'email_verified_at' => now(),
            ]);

            // Assign default role
            $user->assignRole('user');
        }

        // Create social identity
        SocialIdentity::findOrCreateForProvider($provider, $socialUser, $user);

        return $user;
    }
}
