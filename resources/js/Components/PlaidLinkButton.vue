<template>
    <button
        @click="initiatePlaidLink"
        :disabled="loading || syncing"
        class="px-4 py-2 rounded-lg bg-[#155EEF] hover:bg-[#1349CC] disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center text-[#F5F5F6] font-semibold transition-colors"
    >
        <svg
            v-if="loading || syncing"
            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="mr-2">
            <path d="M10 3.33334V16.6667M3.33334 10H16.6667" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        {{ loading ? 'Loading...' : syncing ? 'Connecting...' : 'Connect Bank Account' }}
    </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const loading = ref(false);
const syncing = ref(false);
const plaidHandler = ref(null);

onMounted(() => {
    // Load Plaid Link script
    if (!document.querySelector('script[src*="plaid.com"]')) {
        const script = document.createElement('script');
        script.src = 'https://cdn.plaid.com/link/v2/stable/link-initialize.js';
        script.async = true;
        document.head.appendChild(script);
    }
});

const initiatePlaidLink = async () => {
    loading.value = true;
    
    try {
        // Get Link token from backend
        const response = await fetch('/api/v1/plaid/link/token/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to create Link token');
        }

        const data = await response.json();
        
        // Initialize Plaid Link
        if (window.Plaid) {
            plaidHandler.value = window.Plaid.create({
                token: data.link_token,
                onSuccess: async (publicToken, metadata) => {
                    await handlePlaidSuccess(publicToken, metadata);
                },
                onExit: (err, metadata) => {
                    loading.value = false;
                    syncing.value = false;
                    if (err) {
                        console.error('Plaid Link error:', err);
                        alert('Failed to connect account. Please try again.');
                    }
                },
                onEvent: (eventName, metadata) => {
                    console.log('Plaid event:', eventName, metadata);
                },
            });

            plaidHandler.value.open();
            loading.value = false;
        } else {
            throw new Error('Plaid Link library not loaded');
        }
    } catch (error) {
        console.error('Error initiating Plaid Link:', error);
        alert('Failed to initialize Plaid Link. Please try again.');
        loading.value = false;
    }
};

const handlePlaidSuccess = async (publicToken, metadata) => {
    syncing.value = true;

    try {
        const response = await fetch('/api/v1/plaid/link/token/exchange', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                public_token: publicToken,
                metadata: metadata,
            }),
        });

        if (!response.ok) {
            throw new Error('Failed to exchange token');
        }

        const result = await response.json();
        
        // Refresh the page to show newly connected accounts
        router.reload({ only: ['cashAccounts', 'creditCards', 'loans'] });
        
        alert(`Successfully connected ${result.accounts.length} account(s)!`);
    } catch (error) {
        console.error('Error exchanging Plaid token:', error);
        alert('Failed to connect accounts. Please try again.');
    } finally {
        syncing.value = false;
    }
};
</script>
