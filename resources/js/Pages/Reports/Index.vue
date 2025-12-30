<template>
    <Head :title="'Reports & Analytics'"/>

    <div class="w-full flex flex-col">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="font-semibold font-sans text-[#F5F5F6] text-3xl">Reports & Analytics</h1>
                <p class="text-sm text-gray-400 mt-2">
                    Analyze your financial data and track trends over time
                </p>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="flex items-center gap-4 mb-6 p-4 bg-[#111827] rounded-lg border border-[#333741]">
            <div class="flex flex-col">
                <label class="text-xs text-gray-400 mb-1">Start Date</label>
                <input 
                    type="date" 
                    v-model="filters.start_date"
                    @change="updateFilters"
                    class="rounded-md bg-[#020617] border border-[#333741] text-[#CECFD2] py-2 px-3 text-sm"
                />
            </div>
            <div class="flex flex-col">
                <label class="text-xs text-gray-400 mb-1">End Date</label>
                <input 
                    type="date" 
                    v-model="filters.end_date"
                    @change="updateFilters"
                    class="rounded-md bg-[#020617] border border-[#333741] text-[#CECFD2] py-2 px-3 text-sm"
                />
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-6 bg-[#111827] rounded-lg border border-[#333741]">
                <div class="text-sm text-gray-400 mb-1">Total Income</div>
                <div class="text-2xl font-semibold text-green-400">{{ currency.format(summary.totalIncome) }}</div>
                <div class="text-xs text-gray-500 mt-2">Avg: {{ currency.format(summary.avgMonthlyIncome) }}/mo</div>
            </div>
            <div class="p-6 bg-[#111827] rounded-lg border border-[#333741]">
                <div class="text-sm text-gray-400 mb-1">Total Expenses</div>
                <div class="text-2xl font-semibold text-red-400">{{ currency.format(summary.totalExpenses) }}</div>
                <div class="text-xs text-gray-500 mt-2">Avg: {{ currency.format(summary.avgMonthlyExpenses) }}/mo</div>
            </div>
            <div class="p-6 bg-[#111827] rounded-lg border border-[#333741]">
                <div class="text-sm text-gray-400 mb-1">Net Savings</div>
                <div class="text-2xl font-semibold" :class="summary.netSavings >= 0 ? 'text-green-400' : 'text-red-400'">
                    {{ currency.format(summary.netSavings) }}
                </div>
                <div class="text-xs text-gray-500 mt-2">Savings Rate: {{ summary.savingsRate.toFixed(1) }}%</div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="mb-6 p-6 bg-[#111827] rounded-lg border border-[#333741]">
            <h2 class="text-lg font-semibold text-[#F5F5F6] mb-4">Monthly Income vs Expenses</h2>
            <div v-if="monthlyTrend.length > 0" class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-[#333741]">
                            <th class="text-left text-sm font-medium text-gray-400 pb-3">Month</th>
                            <th class="text-right text-sm font-medium text-gray-400 pb-3">Income</th>
                            <th class="text-right text-sm font-medium text-gray-400 pb-3">Expenses</th>
                            <th class="text-right text-sm font-medium text-gray-400 pb-3">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="month in monthlyTrend" :key="month.month" class="border-b border-[#1F242F]">
                            <td class="py-3 text-sm text-gray-100">{{ formatMonth(month.month) }}</td>
                            <td class="py-3 text-sm text-right text-green-400">{{ currency.format(month.income) }}</td>
                            <td class="py-3 text-sm text-right text-red-400">{{ currency.format(month.expenses) }}</td>
                            <td class="py-3 text-sm text-right font-semibold" :class="month.net >= 0 ? 'text-green-400' : 'text-red-400'">
                                {{ currency.format(month.net) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
                No transaction data available for the selected period
            </div>
        </div>

        <!-- Category Breakdown -->
        <div class="p-6 bg-[#111827] rounded-lg border border-[#333741]">
            <h2 class="text-lg font-semibold text-[#F5F5F6] mb-4">Top Spending Categories</h2>
            <div v-if="categoryBreakdown.length > 0" class="space-y-3">
                <div v-for="(item, index) in categoryBreakdown" :key="index" class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-gray-400 text-sm w-6">{{ index + 1 }}.</span>
                        <span class="text-xs font-sans font-medium leading-[18px] px-[6px] py-[2px] inline-flex items-center border border-[#333741] rounded-md">
                            <span :style="{ backgroundColor: getCategoryColor(item.color) }" class="w-2 h-2 rounded-full mr-1"></span>
                            {{ item.category }}
                        </span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-48 h-2 bg-[#020617] rounded-full overflow-hidden">
                            <div 
                                class="h-2 bg-[#155EEF]"
                                :style="{ width: `${(item.total / categoryBreakdown[0].total) * 100}%` }"
                            />
                        </div>
                        <span class="text-sm font-semibold text-gray-100 w-32 text-right">{{ currency.format(item.total) }}</span>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
                No category spending data available for the selected period
            </div>
        </div>
    </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
    layout: AuthenticatedLayout
};
</script>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useFormatters } from '@/Composables/useFormatters';
import { useCategoryColor } from '@/Composables/useCategoryColor.js';

const monthlyTrend = computed(() => usePage().props.monthlyTrend || []);
const categoryBreakdown = computed(() => usePage().props.categoryBreakdown || []);
const summary = computed(() => usePage().props.summary);

const filters = ref({
    start_date: usePage().props.filters.start_date,
    end_date: usePage().props.filters.end_date,
});

const { currency } = useFormatters();
const { getCategoryColor } = useCategoryColor();

const formatMonth = (monthString) => {
    const [year, month] = monthString.split('-');
    const date = new Date(year, month - 1);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};

const updateFilters = () => {
    router.get('/reports', filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

</script>
