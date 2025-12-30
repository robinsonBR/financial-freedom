<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\CreditCard;
use App\Models\Institution;
use App\Models\Loan;
use App\Models\Transaction;
use App\Services\PlaidService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlaidController extends Controller
{
    public function __construct(
        protected PlaidService $plaidService
    ) {}

    /**
     * Generate a Link token for Plaid Link initialization
     */
    public function createLinkToken(): JsonResponse
    {
        $userId = Auth::id();
        $result = $this->plaidService->createLinkToken($userId);

        if (!$result) {
            return response()->json([
                'error' => 'Failed to create Link token'
            ], 500);
        }

        return response()->json([
            'link_token' => $result['link_token'],
            'expiration' => $result['expiration'],
        ]);
    }

    /**
     * Exchange public token and create connected accounts
     */
    public function exchangeToken(Request $request): JsonResponse
    {
        $request->validate([
            'public_token' => 'required|string',
            'metadata' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            // Exchange public token for access token
            $tokenData = $this->plaidService->exchangePublicToken($request->public_token);
            
            if (!$tokenData) {
                throw new \Exception('Failed to exchange public token');
            }

            $accessToken = $tokenData['access_token'];
            $itemId = $tokenData['item_id'];

            // Get accounts from Plaid
            $accountsData = $this->plaidService->getAccounts($accessToken);
            
            if (!$accountsData) {
                throw new \Exception('Failed to fetch accounts');
            }

            // Get or create institution
            $institutionId = $request->metadata['institution']['institution_id'] ?? null;
            $institutionName = $request->metadata['institution']['name'] ?? 'Unknown Bank';
            
            $institution = Institution::firstOrCreate(
                ['plaid_id' => $institutionId],
                ['name' => $institutionName]
            );

            $createdAccounts = [];

            // Create accounts based on their type
            foreach ($accountsData['accounts'] as $plaidAccount) {
                $accountType = $plaidAccount['type'];
                $accountSubtype = $plaidAccount['subtype'];
                $balance = $plaidAccount['balances']['current'] ?? 0;

                $commonData = [
                    'user_id' => Auth::id(),
                    'institution_id' => $institution->id,
                    'name' => $plaidAccount['name'],
                    'connection_type' => 'plaid',
                    'plaid_access_token' => $accessToken,
                    'plaid_item_id' => $itemId,
                    'plaid_account_id' => $plaidAccount['account_id'],
                    'plaid_institution_id' => $institutionId,
                    'last_synced_at' => now(),
                    'sync_status' => 'success',
                ];

                // Create appropriate account type
                if ($accountType === 'depository') {
                    $account = CashAccount::create(array_merge($commonData, [
                        'type' => $accountSubtype ?? 'checking',
                        'balance' => $balance,
                        'account_number' => $plaidAccount['mask'] ?? null,
                    ]));
                    $createdAccounts[] = ['type' => 'cash_account', 'id' => $account->id];
                    
                } elseif ($accountType === 'credit') {
                    $creditLimit = $plaidAccount['balances']['limit'] ?? 0;
                    $account = CreditCard::create(array_merge($commonData, [
                        'brand' => $institutionName,
                        'balance' => $balance,
                        'credit_limit' => $creditLimit,
                    ]));
                    $createdAccounts[] = ['type' => 'credit_card', 'id' => $account->id];
                    
                } elseif ($accountType === 'loan') {
                    $account = Loan::create(array_merge($commonData, [
                        'type' => $accountSubtype ?? 'personal',
                        'remaining_balance' => $balance,
                        'original_balance' => $balance,
                    ]));
                    $createdAccounts[] = ['type' => 'loan', 'id' => $account->id];
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Accounts connected successfully',
                'accounts' => $createdAccounts,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Plaid token exchange failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'error' => 'Failed to connect accounts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync account balance from Plaid
     */
    public function syncBalance(Request $request): JsonResponse
    {
        $request->validate([
            'account_type' => 'required|in:cash_account,credit_card,loan',
            'account_id' => 'required|integer',
        ]);

        $accountType = $request->account_type;
        $accountId = $request->account_id;

        // Get the appropriate model
        $account = match ($accountType) {
            'cash_account' => CashAccount::where('user_id', Auth::id())->find($accountId),
            'credit_card' => CreditCard::where('user_id', Auth::id())->find($accountId),
            'loan' => Loan::where('user_id', Auth::id())->find($accountId),
        };

        if (!$account || $account->connection_type !== 'plaid') {
            return response()->json(['error' => 'Account not found or not connected via Plaid'], 404);
        }

        try {
            $account->update(['sync_status' => 'syncing']);

            $balanceData = $this->plaidService->getBalance($account->plaid_access_token);
            
            if (!$balanceData) {
                throw new \Exception('Failed to fetch balance');
            }

            // Find matching account in response
            $plaidAccount = collect($balanceData['accounts'])
                ->firstWhere('account_id', $account->plaid_account_id);

            if (!$plaidAccount) {
                throw new \Exception('Account not found in Plaid response');
            }

            $newBalance = $plaidAccount['balances']['current'];

            // Update balance based on account type
            if ($accountType === 'cash_account') {
                $account->update([
                    'balance' => $newBalance,
                    'sync_status' => 'success',
                    'last_synced_at' => now(),
                    'sync_error' => null,
                ]);
            } elseif ($accountType === 'credit_card') {
                $account->update([
                    'balance' => $newBalance,
                    'credit_limit' => $plaidAccount['balances']['limit'] ?? $account->credit_limit,
                    'sync_status' => 'success',
                    'last_synced_at' => now(),
                    'sync_error' => null,
                ]);
            } elseif ($accountType === 'loan') {
                $account->update([
                    'remaining_balance' => $newBalance,
                    'sync_status' => 'success',
                    'last_synced_at' => now(),
                    'sync_error' => null,
                ]);
            }

            return response()->json([
                'message' => 'Balance synced successfully',
                'balance' => $newBalance,
                'synced_at' => $account->last_synced_at,
            ]);

        } catch (\Exception $e) {
            $account->update([
                'sync_status' => 'error',
                'sync_error' => $e->getMessage(),
            ]);

            Log::error('Plaid balance sync failed', [
                'error' => $e->getMessage(),
                'account_type' => $accountType,
                'account_id' => $accountId,
            ]);

            return response()->json([
                'error' => 'Failed to sync balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync transactions from Plaid
     */
    public function syncTransactions(Request $request): JsonResponse
    {
        $request->validate([
            'account_type' => 'required|in:cash_account,credit_card,loan',
            'account_id' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $accountType = $request->account_type;
        $accountId = $request->account_id;

        $account = match ($accountType) {
            'cash_account' => CashAccount::where('user_id', Auth::id())->find($accountId),
            'credit_card' => CreditCard::where('user_id', Auth::id())->find($accountId),
            'loan' => Loan::where('user_id', Auth::id())->find($accountId),
        };

        if (!$account || $account->connection_type !== 'plaid') {
            return response()->json(['error' => 'Account not found or not connected via Plaid'], 404);
        }

        try {
            $account->update(['sync_status' => 'syncing']);

            $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
            $endDate = $request->end_date ?? now()->format('Y-m-d');

            $transactionsData = $this->plaidService->getTransactions(
                $account->plaid_access_token,
                $startDate,
                $endDate,
                $account->plaid_account_id
            );

            if (!$transactionsData) {
                throw new \Exception('Failed to fetch transactions');
            }

            $imported = 0;
            $skipped = 0;

            foreach ($transactionsData['transactions'] as $plaidTx) {
                // Check if transaction already exists
                $exists = Transaction::where('plaid_transaction_id', $plaidTx['transaction_id'])->exists();
                
                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Map account polymorphic relationship
                $accountableType = match ($accountType) {
                    'cash_account' => CashAccount::class,
                    'credit_card' => CreditCard::class,
                    'loan' => Loan::class,
                };

                Transaction::create([
                    'user_id' => Auth::id(),
                    'accountable_type' => $accountableType,
                    'accountable_id' => $account->id,
                    'date' => $plaidTx['date'],
                    'name' => $plaidTx['name'],
                    'amount' => abs($plaidTx['amount']),
                    'type' => $plaidTx['amount'] > 0 ? 'expense' : 'income',
                    'plaid_transaction_id' => $plaidTx['transaction_id'],
                    'merchant_name' => $plaidTx['merchant_name'] ?? null,
                    'pending' => $plaidTx['pending'] ?? false,
                ]);

                $imported++;
            }

            $account->update([
                'sync_status' => 'success',
                'last_synced_at' => now(),
                'sync_error' => null,
            ]);

            return response()->json([
                'message' => 'Transactions synced successfully',
                'imported' => $imported,
                'skipped' => $skipped,
                'total' => count($transactionsData['transactions']),
            ]);

        } catch (\Exception $e) {
            $account->update([
                'sync_status' => 'error',
                'sync_error' => $e->getMessage(),
            ]);

            Log::error('Plaid transactions sync failed', [
                'error' => $e->getMessage(),
                'account_type' => $accountType,
                'account_id' => $accountId,
            ]);

            return response()->json([
                'error' => 'Failed to sync transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect Plaid account
     */
    public function disconnect(Request $request): JsonResponse
    {
        $request->validate([
            'account_type' => 'required|in:cash_account,credit_card,loan',
            'account_id' => 'required|integer',
        ]);

        $accountType = $request->account_type;
        $accountId = $request->account_id;

        $account = match ($accountType) {
            'cash_account' => CashAccount::where('user_id', Auth::id())->find($accountId),
            'credit_card' => CreditCard::where('user_id', Auth::id())->find($accountId),
            'loan' => Loan::where('user_id', Auth::id())->find($accountId),
        };

        if (!$account || $account->connection_type !== 'plaid') {
            return response()->json(['error' => 'Account not found or not connected via Plaid'], 404);
        }

        try {
            // Remove item from Plaid
            $this->plaidService->removeItem($account->plaid_access_token);

            // Clear Plaid data from account
            $account->update([
                'connection_type' => 'manual',
                'plaid_access_token' => null,
                'plaid_item_id' => null,
                'plaid_account_id' => null,
                'plaid_institution_id' => null,
                'sync_status' => 'idle',
                'sync_error' => null,
            ]);

            return response()->json([
                'message' => 'Account disconnected successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Plaid disconnect failed', [
                'error' => $e->getMessage(),
                'account_type' => $accountType,
                'account_id' => $accountId,
            ]);

            return response()->json([
                'error' => 'Failed to disconnect account: ' . $e->getMessage()
            ], 500);
        }
    }
}
