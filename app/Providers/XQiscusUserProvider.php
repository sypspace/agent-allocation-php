<?php

namespace App\Providers;

// use Illuminate\Support\ServiceProvider;

use App\Models\User;
use App\Services\QisusAuthService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class QiscusProvider extends UserProvider
{
    protected QisusAuthService $authService;

    public function __construct(QisusAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials['username']) || empty($credentials['password'])) {
            return null;
        }

        $userData = $this->authService->authenticate($credentials['username'], $credentials['password']);

        if ($userData) {
            // Find or create a local user instance
            return User::firstOrCreate(
                ['email' => $userData['data']['user']['email']],
                [
                    'name' => $userData['data']['user']['name'],
                    'password' => bcrypt($credentials['password']),
                ]
            );
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $user->getAuthPassword() === bcrypt($credentials['password']);
    }
}
