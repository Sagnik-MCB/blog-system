<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialIdentity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_id',
        'access_token',
        'refresh_token',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the user that owns this social identity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Find or create a social identity for a provider.
     */
    public static function findOrCreateForProvider(string $provider, object $socialUser, User $user): self
    {
        return static::updateOrCreate(
            [
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
            ],
            [
                'user_id' => $user->id,
                'access_token' => $socialUser->token ?? null,
                'refresh_token' => $socialUser->refreshToken ?? null,
            ]
        );
    }
}

