<template>
    <div class="inline-flex items-center space-x-2">
        <!-- Connection status badge -->
        <span v-if="account.connection_type === 'plaid'" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="statusClasses">
            <svg v-if="account.sync_status === 'syncing'" class="animate-spin h-3 w-3 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else-if="account.sync_status === 'success'" class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <svg v-else-if="account.sync_status === 'error'" class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            {{ statusText }}
        </span>

        <!-- Sync button for Plaid accounts -->
        <button
            v-if="account.connection_type === 'plaid'"
            @click="syncAccount"
            :disabled="syncing"
            class="inline-flex items-center px-2 py-1 text-xs font-medium text-[#155EEF] hover:text-[#1349CC] disabled:opacity-50 disabled:cursor-not-allowed"
            title="Sync balance and transactions"
        >
            <svg
                :class="{ 'animate-spin': syncing }"
                class="h-4 w-4"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>

        <!-- Disconnect button -->
        <button
            v-if="account.connection_type === 'plaid'"
            @click="disconnectAccount"
            :disabled="disconnecting"
            class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 disabled:opacity-50 disabled:cursor-not-allowed"
            title="Disconnect from Plaid"
        >
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
        </button>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    account: {
        type: Object,
        required: true
    },
    accountType: {
        type: String,
        required: true,
        validator: (value) => ['cash_account', 'credit_card', 'loan'].includes(value)
    }
});

const syncing = ref(false);
const disconnecting = ref(false);

const statusText = computed(() => {
    if (!props.account.connection_type || props.account.connection_type === 'manual') {
        return 'Manual';
    }

    switch (props.account.sync_status) {
        case 'syncing':
            return 'Syncing...';
        case 'success':
            return 'Synced';
        case 'error':
            return 'Error';
        default:
            return 'Connected';
    }
});

const statusClasses = computed(() => {
    if (!props.account.connection_type || props.account.connection_type === 'manual') {
        return 'bg-gray-100 text-gray-800';
    }

    switch (props.account.sync_status) {
        case 'syncing':
            return 'bg-blue-100 text-blue-800';
        case 'success':
            return 'bg-green-100 text-green-800';
        case 'error':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});

const syncAccount = async () => {
    if (syncing.value) return;

    syncing.value = true;

    try {
        // Sync balance
        const balanceResponse = await fetch('/api/v1/plaid/accounts/sync-balance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                account_type: props.accountType,
                account_id: props.account.id,
            }),
        });

        if (!balanceResponse.ok) {
            throw new Error('Failed to sync balance');
        }

        // Sync transactions
        const transactionsResponse = await fetch('/api/v1/plaid/accounts/sync-transactions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                account_type: props.accountType,
                account_id: props.account.id,
            }),
        });

        if (!transactionsResponse.ok) {
            throw new Error('Failed to sync transactions');
        }

        const result = await transactionsResponse.json();

        // Reload page data
        router.reload({ only: ['cashAccounts', 'creditCards', 'loans'] });

        alert(`Synced successfully! Imported ${result.imported} transaction(s), skipped ${result.skipped} duplicate(s).`);
    } catch (error) {
        console.error('Error syncing account:', error);
        alert('Failed to sync account. Please try again.');
    } finally {
        syncing.value = false;
    }
};

const disconnectAccount = async () => {
    if (disconnecting.value) return;

    if (!confirm('Are you sure you want to disconnect this account from Plaid? The account will remain but will need to be updated manually.')) {
        return;
    }

    disconnecting.value = true;

    try {
        const response = await fetch('/api/v1/plaid/accounts/disconnect', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                account_type: props.accountType,
                account_id: props.account.id,
            }),
        });

        if (!response.ok) {
            throw new Error('Failed to disconnect account');
        }

        // Reload page data
        router.reload({ only: ['cashAccounts', 'creditCards', 'loans'] });

        alert('Account disconnected successfully. You can now manage it manually.');
    } catch (error) {
        console.error('Error disconnecting account:', error);
        alert('Failed to disconnect account. Please try again.');
    } finally {
        disconnecting.value = false;
    }
};
</script>
