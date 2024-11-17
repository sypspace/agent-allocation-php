<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class QiscusAuthService
{
    protected $baseUrl;
    protected $appId;

    public function __construct()
    {
        $this->baseUrl = env('QISCUS_BASE_URL');
        $this->appId = env('QISCUS_APP_ID');
    }

    /**
     * Auto-login the user on application startup.
     */
    public function getToken()
    {
        // Predefined credentials for auto-login
        $username = env('QISCUS_EMAIL');
        $password = env('QISCUS_PASSWORD');

        $authToken = Session::get(env('QISCUS_TOKEN'));

        if (!$authToken) {
            // Attempt to authenticate with the server
            $response = $this->authenticate($username, $password);
            $authToken = $response['data']['user']['authentication_token'];

            // Store the token in the session if successful
            if ($authToken) {
                Session::put(env('QISCUS_TOKEN'), $authToken);
            }
        }

        return $authToken;
    }

    /**
     * Handle token expiry.
     */
    public function refreshTokenIfNeeded()
    {
        $authToken = Session::get(env('QISCUS_TOKEN'));

        // If no token is stored or it's expired, re-authenticate
        if (!$authToken || $this->isTokenExpired($authToken)) {
            $this->getToken();
        }
    }

    protected function isTokenExpired(string $token): bool
    {
        return false; // For simplicity, assume it's never expired unless specified
    }

    /**
     * Authenticate the user using an external API.
     *
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function authenticate(string $username, string $password): ?array
    {
        try {
            $response = Http::qiscus()->asForm()->post("{$this->baseUrl}/api/v1/auth", [
                'email' => $username,
                'password' => $password,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Authentication failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch user profile via external API.
     */
    public function getUserProfile(string $token): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "{$token}",
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])
                ->get("{$this->baseUrl}/api/v1/admin/get_profile");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get user profile: ' . $e->getMessage());
            return null;
        }
    }
}
