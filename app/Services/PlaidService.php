<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class PlaidService
{
    protected Client $client;
    protected string $clientId;
    protected string $secret;
    protected string $environment;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.plaid.client_id');
        $this->secret = config('services.plaid.secret');
        $this->environment = config('services.plaid.environment', 'sandbox');
        
        // Set base URL based on environment
        $this->baseUrl = match ($this->environment) {
            'production' => 'https://production.plaid.com',
            'development' => 'https://development.plaid.com',
            default => 'https://sandbox.plaid.com',
        };

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Create a Link token for initializing Plaid Link
     */
    public function createLinkToken(int $userId): ?array
    {
        try {
            $response = $this->client->post('/link/token/create', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'user' => [
                        'client_user_id' => (string) $userId,
                    ],
                    'client_name' => config('app.name'),
                    'products' => ['transactions'],
                    'country_codes' => ['US'],
                    'language' => 'en',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Plaid Link Token creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return null;
        }
    }

    /**
     * Exchange public token for access token
     */
    public function exchangePublicToken(string $publicToken): ?array
    {
        try {
            $response = $this->client->post('/item/public_token/exchange', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'public_token' => $publicToken,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Plaid public token exchange failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get accounts for a given access token
     */
    public function getAccounts(string $accessToken): ?array
    {
        try {
            $response = $this->client->post('/accounts/get', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'access_token' => $accessToken,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Plaid get accounts failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get account balance
     */
    public function getBalance(string $accessToken): ?array
    {
        try {
            $response = $this->client->post('/accounts/balance/get', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'access_token' => $accessToken,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Plaid get balance failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get transactions for a date range
     */
    public function getTransactions(
        string $accessToken,
        string $startDate,
        string $endDate,
        ?string $accountId = null
    ): ?array {
        try {
            $payload = [
                'client_id' => $this->clientId,
                'secret' => $this->secret,
                'access_token' => $accessToken,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];

            if ($accountId) {
                $payload['options'] = ['account_ids' => [$accountId]];
            }

            $response = $this->client->post('/transactions/get', [
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Plaid get transactions failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get institution information
     */
    public function getInstitution(string $institutionId): ?array
    {
        try {
            $response = $this->client->post('/institutions/get_by_id', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'institution_id' => $institutionId,
                    'country_codes' => ['US'],
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Plaid get institution failed', [
                'error' => $e->getMessage(),
                'institution_id' => $institutionId,
            ]);
            return null;
        }
    }

    /**
     * Remove an item (disconnect account)
     */
    public function removeItem(string $accessToken): bool
    {
        try {
            $this->client->post('/item/remove', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'access_token' => $accessToken,
                ],
            ]);

            return true;
        } catch (GuzzleException $e) {
            Log::error('Plaid remove item failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get item status
     */
    public function getItemStatus(string $accessToken): ?array
    {
        try {
            $response = $this->client->post('/item/get', [
                'json' => [
                    'client_id' => $this->clientId,
                    'secret' => $this->secret,
                    'access_token' => $accessToken,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Plaid get item status failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
