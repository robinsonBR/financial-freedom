<template>
  <div>
    <Head title="Budget Plan" />
    <h1 class="text-2xl font-semibold text-gray-100 mb-4">Budget Plan</h1>

    <p class="text-sm text-gray-400 mb-4">
      Showing categories for {{ year }}-{{ month.toString().padStart(2, '0') }}. Planned amounts will come next; for now this page is wired and ready.
    </p>

    <div v-if="rows.length" class="mt-4 border border-[#333741] rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-[#333741]">
        <thead class="bg-[#111827]">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
              Category
            </th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">
              Planned
            </th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">
              Actual
            </th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">
              Utilization
            </th>
          </tr>
        </thead>
        <tbody class="bg-[#020617] divide-y divide-[#111827]">
          <tr v-for="row in rows" :key="row.categoryId">
            <td class="px-4 py-2 text-sm text-gray-100">
              {{ row.name }}
            </td>
            <td class="px-4 py-2 text-sm text-right text-gray-100">
              {{ currency.format(row.planned) }}
            </td>
            <td class="px-4 py-2 text-sm text-right text-gray-100">
              {{ currency.format(row.actual) }}
            </td>
            <td class="px-4 py-2 text-sm text-right text-gray-100">
              <div class="inline-flex items-center space-x-2">
                <span>
                  {{ (row.utilization * 100).toFixed(0) }}%
                </span>
                <div class="w-16 h-2 bg-[#111827] rounded-full overflow-hidden">
                  <div
                    class="h-2 bg-[#155EEF]"
                    :style="{ width: `${Math.round(row.utilization * 100)}%` }"
                  />
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-else class="text-sm text-gray-500 mt-4">
      You don't have any categories with budgets yet. Create categories in Settings → Categories and assign monthly amounts to start budgeting.
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
import { aggregateActualByCategory } from '@/Domain/budget/aggregateBudget';
import { calculateUtilization } from '@/Domain/budget/utilization';

type Category = {
  id: number;
  name: string;
  monthly_budget: number;
};

type BudgetPageProps = {
  year: number;
  month: number;
  categories: Category[];
  transactions: Array<{
    category_id: number | null;
    amount: number | string;
    date: string;
  }>;
};

const page = usePage<BudgetPageProps>();

const year = page.props.year;
const month = page.props.month;
const categories = page.props.categories ?? [];
const transactions = page.props.transactions ?? [];

const { currency } = useFormatters();

const rows = computed(() => {
  const txForHelper = transactions.map((tx) => ({
    categoryId: tx.category_id,
    amount: typeof tx.amount === 'string' ? parseFloat(tx.amount) : tx.amount,
    date: tx.date,
  }));

  const actualByCategory = aggregateActualByCategory(txForHelper, year, month);

  return categories.map((category) => {
    const key = String(category.id);
    const actual = actualByCategory[key] ?? 0;

    const utilization = calculateUtilization({
      planned: category.monthly_budget ?? 0,
      actual,
    });

    return {
      categoryId: category.id,
      name: category.name,
      planned: category.monthly_budget ?? 0,
      actual,
      utilization,
    };
  });
});
</script>
