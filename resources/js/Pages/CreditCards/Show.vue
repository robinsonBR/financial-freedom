<template>
    <div>
        <Head :title="creditCard.name + ' - Credit Card'" />

        <div class="space-y-6">
            <div class="flex w-full justify-between">
                <div>
                    <h1 class="font-semibold font-sans text-[#F5F5F6] text-3xl">{{ creditCard.name }}</h1>
                    <p class="text-sm text-gray-400">
                        {{ creditCard.institution?.name ?? 'Unlinked institution' }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <button @click="addTransaction" class="px-[14px] py-[10px] rounded-lg bg-[#155EEF] flex items-center text-[#F5F5F6] font-semibold">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" class="mr-[6px]">
                            <path d="M7.00033 1.16669V12.8334M1.16699 7.00002H12.8337" stroke="white" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Add Transaction
                    </button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
                    <p class="text-xs text-gray-400 uppercase mb-1">Current Balance</p>
                    <p class="text-2xl font-semibold text-gray-50">
                        {{ currency.format(creditCard.current_balance) }}
                    </p>
                </div>
                <div class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
                    <p class="text-xs text-gray-400 uppercase mb-1">Credit Limit</p>
                    <p class="text-lg font-semibold text-gray-100">
                        {{ creditCard.limit ? currency.format(creditCard.limit) : 'N/A' }}
                    </p>
                </div>
                <div class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
                    <p class="text-xs text-gray-400 uppercase mb-1">Utilization</p>
                    <p class="text-lg font-semibold" :class="utilization > 0.3 ? 'text-amber-300' : 'text-emerald-400'">
                        {{ (utilization * 100).toFixed(1) }}%
                    </p>
                </div>
            </div>

            <div v-if="creditCard.description" class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
                <p class="text-xs text-gray-400 uppercase mb-1">Description</p>
                <p class="text-sm text-gray-200">{{ creditCard.description }}</p>
            </div>

            <div class="border border-[#333741] rounded-lg overflow-hidden" v-if="transactions.length">
                <table class="min-w-full divide-y divide-[#333741]">
                    <thead class="bg-[#111827]">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Merchant</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#020617] divide-y divide-[#111827]">
                        <tr v-for="tx in transactions" :key="tx.id">
                            <td class="px-4 py-2 text-sm text-gray-100">{{ tx.date }}</td>
                            <td class="px-4 py-2 text-sm text-gray-100">{{ tx.merchant ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-100">{{ tx.category?.name ?? 'Uncategorized' }}</td>
                            <td
                                class="px-4 py-2 text-sm text-right"
                                :class="tx.type === 'credit' ? 'text-emerald-400' : 'text-rose-400'"
                            >
                                {{ currency.format(tx.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="text-sm text-gray-500">No transactions for this card yet.</p>
        </div>
    </div>
</template>

<script lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
    layout: AuthenticatedLayout,
};
</script>

<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useEventBus } from '@vueuse/core';
import { useFormatters } from '@/Composables/useFormatters';

interface Institution {
    id: number;
    name: string;
}

interface CreditCard {
    id: number;
    name: string;
    description: string | null;
    current_balance: number;
    limit: number | null;
    institution: Institution | null;
}

interface CreditCardTransaction {
    id: number;
    date: string;
    amount: number | string;
    type: 'credit' | 'debit';
    merchant: string | null;
    category: { id: number; name: string } | null;
}

interface CreditCardShowProps {
    creditCard: CreditCard;
    transactions: CreditCardTransaction[];
}

const page = usePage<CreditCardShowProps>();
const creditCard = page.props.creditCard;
const rawTransactions = page.props.transactions ?? [];

const transactions = rawTransactions.map((tx) => ({
    ...tx,
    amount: typeof tx.amount === 'string' ? parseFloat(tx.amount) : tx.amount,
}));

const { currency } = useFormatters();

const utilization = computed(() => {
    if (!creditCard.limit || creditCard.limit <= 0) return 0;
    return Math.min(Math.max(creditCard.current_balance / creditCard.limit, 0), 1);
}).value;

const promptBus = useEventBus('ff-prompt-event-bus');

const addTransaction = () => {
    promptBus.emit('prompt', {
        type: 'add-transaction',
        payload: {
            accountType: 'credit-card',
            accountId: creditCard.id,
        },
    });
};
</script>