<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends Controller
{
    /**
     * Supported social providers.
     */
    protected array $providers = ['google', 'facebook'];

    /**
     * Redirect the user to the provider's authentication page.
     */
    public function redirect(string $provider): RedirectResponse
    {
        if (!in_array($provider, $this->providers)) {
            return redirect()->route('login')
                ->with('error', 'Invalid authentication provider.');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the provider.
     */
    public function callback(string $provider): RedirectResponse
    {
        if (!in_array($provider, $this->providers)) {
            return redirect()->route('login')
                ->with('error', 'Invalid authentication provider.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Find or create user
            $user = User::findOrCreateFromSocialite($socialUser, $provider);

            // Check if user is active
            if (!$user->is_active) {
                return redirect()->route('login')
                    ->with('error', 'Your account has been deactivated. Please contact support.');
            }

            // Log the user in
            Auth::login($user, true);

            // Log activity
            ActivityLog::log(
                action: 'social_login',
                description: "User logged in via {$provider}",
                model: $user,
                properties: ['provider' => $provider]
            );

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');

        } catch (Exception $e) {
            report($e);
            
            return redirect()->route('login')
                ->with('error', 'Unable to authenticate with ' . ucfirst($provider) . '. Please try again.');
        }
    }
}

