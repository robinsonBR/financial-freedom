<template>
	<div>
		<Head :title="`${cashAccount.name} - Cash Account`" />

		<div class="space-y-6">
			<div class="flex items-center justify-between">
				<div>
					<h1 class="text-2xl font-semibold text-gray-100">{{ cashAccount.name }}</h1>
					<p class="text-sm text-gray-400">
						{{ cashAccount.institution?.name ?? 'Unlinked institution' }} · {{ cashAccount.type }}
					</p>
				</div>
			</div>

			<div class="grid gap-4 md:grid-cols-3">
				<div class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
					<p class="text-xs text-gray-400 uppercase mb-1">Current Balance</p>
					<p class="text-2xl font-semibold text-gray-50">
						{{ currency.format(cashAccount.balance) }}
					</p>
				</div>
				<div class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
					<p class="text-xs text-gray-400 uppercase mb-1">Interest Rate</p>
					<p class="text-lg font-semibold text-gray-100">
						{{ cashAccount.interest_rate ? `${cashAccount.interest_rate}%` : 'N/A' }}
					</p>
				</div>
				<div class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
					<p class="text-xs text-gray-400 uppercase mb-1">This Account Net Flow</p>
					<p class="text-lg font-semibold" :class="net >= 0 ? 'text-emerald-400' : 'text-rose-400'">
						{{ currency.format(net) }}
					</p>
				</div>
			</div>

			<div v-if="cashAccount.description" class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
				<p class="text-xs text-gray-400 uppercase mb-1">Description</p>
				<p class="text-sm text-gray-200">{{ cashAccount.description }}</p>
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
			<p v-else class="text-sm text-gray-500">No transactions for this account yet.</p>
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
import { useFormatters } from '@/Composables/useFormatters';

interface Institution {
	id: number;
	name: string;
}

interface CashAccount {
	id: number;
	name: string;
	description: string | null;
	balance: number;
	interest_rate: number | null;
	type: string;
	institution: Institution | null;
}

interface CashAccountTransaction {
	id: number;
	date: string;
	amount: number | string;
	type: 'credit' | 'debit';
	merchant: string | null;
	category: { id: number; name: string } | null;
}

interface CashAccountShowProps {
	cashAccount: CashAccount;
	transactions: CashAccountTransaction[];
}

const page = usePage<CashAccountShowProps>();
const cashAccount = page.props.cashAccount;
const rawTransactions = page.props.transactions ?? [];

const transactions = rawTransactions.map((tx) => ({
	...tx,
	amount: typeof tx.amount === 'string' ? parseFloat(tx.amount) : tx.amount,
}));

const { currency } = useFormatters();

const totalCredits = computed(() =>
	transactions.reduce((sum, tx) => (tx.type === 'credit' ? sum + (tx.amount as number) : sum), 0)
);

const totalDebits = computed(() =>
	transactions.reduce((sum, tx) => (tx.type === 'debit' ? sum + (tx.amount as number) : sum), 0)
);

const net = computed(() => totalCredits.value - totalDebits.value).value;
</script>