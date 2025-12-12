<template>
  <div>
    <Head title="Cash Flow" />
    <h1 class="text-2xl font-semibold text-gray-100 mb-4">Cash Flow</h1>

    <p class="text-sm text-gray-400 mb-4">
      Cash flow for {{ year }}-{{ month.toString().padStart(2, '0') }}.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <SummaryStatCard
        label="Income"
        :value="currency.format(summary.income)"
        value-class="text-lg font-semibold text-emerald-400"
      />
      <SummaryStatCard
        label="Expenses"
        :value="currency.format(summary.expenses)"
        value-class="text-lg font-semibold text-rose-400"
      />
      <SummaryStatCard label="Net" :value="currency.format(summary.net)" :value-class="netClass" />
    </div>

    <div class="mt-6 border border-[#333741] rounded-lg overflow-hidden" v-if="categorySummary.length">
      <div class="bg-[#111827] px-4 py-2 border-b border-[#333741]">
        <h2 class="text-sm font-medium text-gray-200">By Category</h2>
        <p class="text-xs text-gray-400">Income, expenses, and net by category for this month.</p>
      </div>
      <table class="min-w-full divide-y divide-[#333741]">
        <thead class="bg-[#111827]">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Income</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Expenses</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Net</th>
          </tr>
        </thead>
        <tbody class="bg-[#020617] divide-y divide-[#111827]">
          <tr v-for="row in categorySummary" :key="row.category_id ?? 'uncategorized'">
            <td class="px-4 py-2 text-sm text-gray-100">{{ row.category_name }}</td>
            <td class="px-4 py-2 text-sm text-right text-emerald-400">
              {{ currency.format(row.income) }}
            </td>
            <td class="px-4 py-2 text-sm text-right text-rose-400">
              {{ currency.format(row.expenses) }}
            </td>
            <td class="px-4 py-2 text-sm text-right" :class="row.net >= 0 ? 'text-emerald-400' : 'text-rose-400'">
              {{ currency.format(row.net) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 border border-[#333741] rounded-lg overflow-hidden" v-if="transactions.length">
      <table class="min-w-full divide-y divide-[#333741]">
        <thead class="bg-[#111827]">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Merchant</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
          </tr>
        </thead>
        <tbody class="bg-[#020617] divide-y divide-[#111827]">
          <tr v-for="tx in transactions" :key="tx.id">
            <td class="px-4 py-2 text-sm text-gray-100">{{ tx.date }}</td>
            <td class="px-4 py-2 text-sm text-gray-100">{{ tx.merchant ?? '-' }}</td>
            <td class="px-4 py-2 text-sm text-right" :class="tx.type === 'credit' ? 'text-emerald-400' : 'text-rose-400'">
              {{ currency.format(tx.amount) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-else class="text-sm text-gray-500 mt-4">
      No transactions found for this month.
    </p>
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
import SummaryStatCard from '@/Components/SummaryStatCard.vue';

interface CashFlowSummary {
  income: number;
  expenses: number;
  net: number;
}

interface CashFlowCategorySummaryRow {
  category_id: number | null;
  category_name: string;
  income: number;
  expenses: number;
  net: number;
}

interface CashFlowTransaction {
  id: number;
  date: string;
  amount: number | string;
  type: 'credit' | 'debit';
  merchant: string | null;
  category_id: number | null;
}

interface CashFlowPageProps {
  year: number;
  month: number;
  summary: CashFlowSummary;
  categorySummary: CashFlowCategorySummaryRow[];
  transactions: CashFlowTransaction[];
}

const page = usePage<CashFlowPageProps>();

const year = page.props.year;
const month = page.props.month;
const summary = page.props.summary;
const categorySummary = page.props.categorySummary ?? [];
const rawTransactions = page.props.transactions ?? [];

const transactions = rawTransactions.map((tx) => ({
  ...tx,
  amount: typeof tx.amount === 'string' ? parseFloat(tx.amount) : tx.amount,
}));

const { currency } = useFormatters();

const netClass = computed(() =>
  summary.net >= 0 ? 'text-lg font-semibold text-emerald-400' : 'text-lg font-semibold text-rose-400'
);
</script>
