# Plaid Banking Integration

This application now supports automatic bank account connectivity via Plaid API in addition to manual account management.

## Features

- **Automatic Bank Connections**: Connect real bank accounts using Plaid Link
- **Balance Syncing**: Automatically sync account balances
- **Transaction Import**: Import transactions directly from connected banks
- **Manual Entry Still Supported**: You can still create and manage accounts manually
- **Flexible**: Choose between connected and manual accounts per-account

## Setup

### 1. Get Plaid API Credentials

1. Sign up for a free Plaid account at https://dashboard.plaid.com/signup
2. Get your credentials:
   - Client ID
   - Secret (use Sandbox secret for testing)
3. Add to your `.env` file:

```env
PLAID_CLIENT_ID=your_client_id_here
PLAID_SECRET=your_sandbox_secret_here
PLAID_ENV=sandbox
```

### 2. Environment Options

- `PLAID_ENV=sandbox` - For development/testing (fake bank data)
- `PLAID_ENV=development` - For testing with real credentials
- `PLAID_ENV=production` - For live production use

### 3. How It Works

#### Connecting a Bank Account

1. Go to Accounts page
2. Click "Connect Bank Account" button
3. Plaid Link modal opens
4. Search for your bank
5. Enter credentials (in sandbox, use test credentials)
6. Select accounts to connect
7. Accounts are automatically created in your app

#### Managing Connected Accounts

Each connected account shows:
- **Connection Status Badge**: Shows sync status (Syncing, Synced, Error)
- **Sync Button**: Manual sync for balance and transactions
- **Disconnect Button**: Remove Plaid connection (account remains, becomes manual)

#### Syncing Data

**Automatic on Connect:**
- Account balances are set from Plaid
- Account names come from bank
- Account types are auto-detected

**Manual Sync:**
- Click sync button on any connected account
- Syncs current balance
- Imports last 30 days of transactions
- Duplicate transactions are automatically skipped

## Database Schema

### Accounts Tables (cash_accounts, credit_cards, loans)

New Plaid fields:
- `connection_type`: 'manual' or 'plaid'
- `plaid_access_token`: Encrypted token for API access
- `plaid_item_id`: Plaid item identifier
- `plaid_account_id`: Plaid account identifier
- `plaid_institution_id`: Bank institution ID
- `last_synced_at`: Timestamp of last successful sync
- `sync_status`: 'idle', 'syncing', 'success', 'error'
- `sync_error`: Error message if sync fails

### Transactions Table

New fields:
- `plaid_transaction_id`: Unique Plaid transaction ID (prevents duplicates)
- `merchant_name`: Merchant name from Plaid
- `pending`: Boolean for pending transactions

### Institutions Table

New field:
- `plaid_id`: Plaid institution identifier

## API Endpoints

All endpoints require authentication via Sanctum.

### Create Link Token
`POST /api/v1/plaid/link/token/create`

Returns a link_token for initializing Plaid Link.

### Exchange Public Token
`POST /api/v1/plaid/link/token/exchange`

Body:
```json
{
  "public_token": "public-sandbox-xxx",
  "metadata": { ... }
}
```

Creates connected accounts from Plaid data.

### Sync Balance
`POST /api/v1/plaid/accounts/sync-balance`

Body:
```json
{
  "account_type": "cash_account",
  "account_id": 1
}
```

Updates account balance from Plaid.

### Sync Transactions
`POST /api/v1/plaid/accounts/sync-transactions`

Body:
```json
{
  "account_type": "cash_account",
  "account_id": 1,
  "start_date": "2024-01-01",
  "end_date": "2024-01-31"
}
```

Imports transactions from Plaid.

### Disconnect Account
`POST /api/v1/plaid/accounts/disconnect`

Body:
```json
{
  "account_type": "cash_account",
  "account_id": 1
}
```

Removes Plaid connection (account becomes manual).

## Testing with Plaid Sandbox

Sandbox test credentials:
- Username: `user_good`
- Password: `pass_good`
- MFA: `1234`

This will give you fake transaction data to test with.

## Security Notes

- Access tokens are stored in database (consider encrypting in production)
- HTTPS required for production
- Plaid credentials should never be committed to git
- Use environment variables for all secrets

## Troubleshooting

### "Failed to create Link token"
- Check PLAID_CLIENT_ID and PLAID_SECRET in .env
- Ensure they match your Plaid dashboard

### "Failed to exchange token"
- Check server logs for detailed error
- Verify API credentials are correct
- Ensure PLAID_ENV matches your secret (sandbox/development/production)

### Transactions not importing
- Check date range (defaults to last 30 days)
- Verify account has transactions in Plaid
- Check sync_status and sync_error fields

### Account shows "Error" status
- Click account to view sync_error message
- Common issues: expired credentials, bank connection issue
- May need to re-authenticate through Plaid Link

## Production Deployment

Before going live:

1. Apply for Plaid Production access
2. Update `.env`:
   ```
   PLAID_ENV=production
   PLAID_SECRET=your_production_secret
   ```
3. Consider encrypting access tokens in database
4. Set up Plaid webhooks for automatic updates
5. Implement proper error handling and user notifications
6. Add retry logic for failed syncs

## Support

For Plaid API issues, see: https://plaid.com/docs/
For app-specific issues, check application logs.
