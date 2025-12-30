<template>
    <Head :title="'Rules'"/>

    <div class="w-full flex flex-col">
        <h1 class="font-semibold font-sans text-[#F5F5F6] text-3xl">Transaction Rules</h1>
        <p class="text-sm text-gray-400 mt-2 mb-6">
            Manage rules that automatically categorize and rename transactions during import.
        </p>

        <div v-if="rules.length > 0" class="mt-4 border border-[#333741] rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-[#333741]">
                <thead class="bg-[#111827]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Account
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Search For
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Replace With
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-[#020617] divide-y divide-[#111827]">
                    <tr v-for="rule in rules" :key="rule.id">
                        <td class="px-4 py-3 text-sm text-gray-100">
                            {{ rule.accountable?.name || 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-100">
                            {{ rule.search_string }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-100">
                            {{ rule.replace_string || '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-100">
                            <span v-if="rule.category" class="text-xs font-sans font-medium leading-[18px] px-[6px] py-[2px] inline-flex items-center border border-[#333741] rounded-md">
                                <span :style="{ backgroundColor: getCategoryColor(rule.category.color) }" class="w-2 h-2 rounded-full mr-1"></span>
                                {{ rule.category.name }}
                            </span>
                            <span v-else>-</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <button @click="editRule(rule)" class="text-[#CECFD2] font-sans text-sm font-semibold mr-3">
                                Edit
                            </button>
                            <button @click="deleteRule(rule)" class="text-[#94969C] font-sans text-sm font-semibold">
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="mt-8 text-center py-12">
            <p class="text-sm text-gray-500">
                No rules found. Rules are automatically created when you import transactions and assign categories.
            </p>
        </div>

        <EditRuleSlideout/>
        <DeleteRuleModal/>
    </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
    layout: AuthenticatedLayout
};
</script>

<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCategoryColor } from '@/Composables/useCategoryColor.js';
import { useEventBus } from '@vueuse/core';
import EditRuleSlideout from './Partials/EditRuleSlideout.vue';
import DeleteRuleModal from './Partials/DeleteRuleModal.vue';

const rules = computed(() => usePage().props.rules);
const categories = computed(() => usePage().props.categories);

const { getCategoryColor } = useCategoryColor();
const bus = useEventBus('ff-prompt-event-bus');

const editRule = (rule) => {
    bus.emit('prompt-edit-rule', { rule, categories: categories.value });
};

const deleteRule = (rule) => {
    bus.emit('prompt-delete-rule', rule);
};

</script>
