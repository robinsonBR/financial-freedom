<template>
    <Head title="Dashboard" />

    <div class="space-y-6">
        <h1 class="text-2xl font-semibold text-white">Dashboard</h1>
        <p class="text-sm text-gray-400">
            A quick snapshot of your finances this month.
        </p>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-1">
                <div class="rounded-xl border border-[#1F242F] bg-[#020617] p-4 mb-4">
                    <p class="text-xs text-gray-400 uppercase mb-1">Net Worth</p>
                    <p class="text-2xl font-semibold text-gray-50">
                        {{ currency.format(netWorthValue) }}
                    </p>
                    <p class="mt-2 text-xs text-gray-400">
                        Assets: {{ currency.format(netWorth.assets) }} · Liabilities: {{ currency.format(netWorth.liabilities) }}
                    </p>
                </div>

                <div class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
                    <p class="text-xs text-gray-400 uppercase mb-1">Goals</p>
                    <div v-if="goals.length === 0" class="text-sm text-gray-400">
                        No goals yet. Head to the Goals tab to create one.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="goal in goals"
                            :key="goal.id"
                            class="border border-[#333741] rounded-md p-3"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-medium text-gray-100">{{ goal.name }}</p>
                                <p class="text-xs text-gray-400">{{ Math.round(goal.progress * 100) }}%</p>
                            </div>
                            <p class="text-xs text-gray-400 mb-2">
                                {{ currency.format(goal.current_amount) }} of {{ currency.format(goal.target_amount) }}
                                <span v-if="goal.due_date"> · by {{ goal.due_date }}</span>
                            </p>
                            <div class="h-1.5 w-full rounded-full bg-[#111827] overflow-hidden">
                                <div
                                    class="h-full rounded-full bg-emerald-500"
                                    :style="{ width: `${Math.round(goal.progress * 100)}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
                    <p class="text-xs text-gray-400 uppercase mb-1">Budget This Month</p>
                    <p class="text-sm text-gray-300 mb-1">
                        {{ currency.format(budgetSummary.actual) }} spent of {{ currency.format(budgetSummary.planned) }} planned
                    </p>
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-xs text-gray-400">Utilization</p>
                        <p class="text-xs font-medium" :class="utilizationPercent > 100 ? 'text-rose-400' : 'text-emerald-400'">
                            {{ Math.round(utilizationPercent) }}%
                        </p>
                    </div>
                    <div class="h-2 w-full rounded-full bg-[#111827] overflow-hidden">
                        <div
                            class="h-full rounded-full"
                            :class="utilizationPercent > 100 ? 'bg-rose-500' : 'bg-emerald-500'"
                            :style="{ width: `${Math.min(120, Math.round(utilizationPercent))}%` }"
                        ></div>
                    </div>
                </div>

                <div class="rounded-xl border border-[#1F242F] bg-[#020617] p-4">
                    <p class="text-xs text-gray-400 uppercase mb-1">Cash Flow This Month</p>
                    <p class="text-sm text-gray-300 mb-2">
                        Income {{ currency.format(cashFlowSummary.income) }} · Expenses {{ currency.format(cashFlowSummary.expenses) }}
                    </p>
                    <p class="text-xl font-semibold" :class="cashFlowSummary.net >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                        {{ currency.format(cashFlowSummary.net) }} net
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
    layout: AuthenticatedLayout
};
</script>

<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useFormatters } from '@/Composables/useFormatters';
import { calculateUtilization } from '@/Domain/budget/utilization';

interface NetWorthSummary {
    assets: number;
    liabilities: number;
}

interface BudgetSummary {
    planned: number;
    actual: number;
}

interface CashFlowSummary {
    income: number;
    expenses: number;
    net: number;
}

interface GoalSummary {
    id: number;
    name: string;
    target_amount: number;
    current_amount: number;
    due_date: string | null;
    progress: number;
}

interface DashboardPageProps {
    netWorth: NetWorthSummary;
    budgetSummary: BudgetSummary;
    cashFlowSummary: CashFlowSummary;
    goals: GoalSummary[];
}

const page = usePage<DashboardPageProps>();
const netWorth = page.props.netWorth;
const budgetSummary = page.props.budgetSummary;
const cashFlowSummary = page.props.cashFlowSummary;
const goals = page.props.goals ?? [];

const netWorthValue = computed(() => netWorth.assets - netWorth.liabilities);

const utilization = computed(() =>
    calculateUtilization({
        planned: budgetSummary.planned,
        actual: budgetSummary.actual,
    })
);

const utilizationPercent = computed(() => utilization.value * 100);

const { currency } = useFormatters();
</script>