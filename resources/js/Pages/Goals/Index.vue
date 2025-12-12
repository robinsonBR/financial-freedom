<template>
  <div>
    <Head title="Goals" />

    <h1 class="text-2xl font-semibold text-gray-100 mb-4">Goals</h1>

    <p class="text-sm text-gray-400 mb-6 max-w-xl">
      Track savings goals and see your progress over time.
    </p>

    <div class="grid gap-6 md:grid-cols-3 mb-8">
      <div class="md:col-span-1 rounded-lg border border-[#333741] bg-[#020617] p-4">
        <h2 class="text-sm font-medium text-gray-200 mb-3">Add Goal</h2>

        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="block text-xs font-medium text-gray-400 mb-1">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full rounded-md bg-[#020617] border border-[#333741] px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              required
            />
          </div>

          <div class="mb-3">
            <label class="block text-xs font-medium text-gray-400 mb-1">Target Amount</label>
            <input
              v-model.number="form.target_amount"
              type="number"
              min="0"
              step="0.01"
              class="w-full rounded-md bg-[#020617] border border-[#333741] px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              required
            />
          </div>

          <div class="mb-3">
            <label class="block text-xs font-medium text-gray-400 mb-1">Current Amount (optional)</label>
            <input
              v-model.number="form.current_amount"
              type="number"
              min="0"
              step="0.01"
              class="w-full rounded-md bg-[#020617] border border-[#333741] px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div class="mb-4">
            <label class="block text-xs font-medium text-gray-400 mb-1">Due Date (optional)</label>
            <input
              v-model="form.due_date"
              type="date"
              class="w-full rounded-md bg-[#020617] border border-[#333741] px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <button
            type="submit"
            class="inline-flex items-center justify-center rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-slate-900"
            :disabled="form.processing"
          >
            Save Goal
          </button>
        </form>
      </div>

      <div
        v-if="editingGoal"
        class="md:col-span-1 rounded-lg border border-[#333741] bg-[#020617] p-4"
      >
        <h2 class="text-sm font-medium text-gray-200 mb-3">Edit Goal</h2>

        <form @submit.prevent="submitEdit">
          <div class="mb-3">
            <label class="block text-xs font-medium text-gray-400 mb-1">Name</label>
            <input
              v-model="editForm.name"
              type="text"
              class="w-full rounded-md bg-[#020617] border border-[#333741] px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              required
            />
          </div>

          <div class="mb-3">
            <label class="block text-xs font-medium text-gray-400 mb-1">Target Amount</label>
            <input
              v-model.number="editForm.target_amount"
              type="number"
              min="0"
              step="0.01"
              class="w-full rounded-md bg-[#020617] border border-[#333741] px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              required
            />
          </div>

          <div class="mb-3">
            <label class="block text-xs font-medium text-gray-400 mb-1">Current Amount</label>
            <input
              v-model.number="editForm.current_amount"
              type="number"
              min="0"
              step="0.01"
              class="w-full rounded-md bg-[#020617] border border-[#333741] px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div class="mb-4">
            <label class="block text-xs font-medium text-gray-400 mb-1">Due Date (optional)</label>
            <input
              v-model="editForm.due_date"
              type="date"
              class="w-full rounded-md bg-[#020617] border border-[#333741] px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>

          <div class="flex items-center gap-2">
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-md bg-emerald-500 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-slate-900"
              :disabled="editForm.processing"
            >
              Update Goal
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-md border border-[#333741] px-3 py-2 text-xs font-medium text-gray-300 hover:bg-[#111827]"
              @click="cancelEdit"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>

      <div class="md:col-span-2 space-y-3">
        <div
          v-if="goals.length === 0"
          class="rounded-lg border border-dashed border-[#333741] bg-[#020617] p-4"
        >
          <p class="text-sm text-gray-300">
            No goals configured yet. Add your first goal to start tracking progress.
          </p>
        </div>

        <div
          v-for="goal in goals"
          :key="goal.id"
          class="rounded-lg border border-[#333741] bg-[#020617] p-4"
        >
          <div class="flex items-start justify-between mb-2 gap-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-100 truncate">{{ goal.name }}</p>
              <p class="text-xs text-gray-400">
                {{ currency.format(goal.current_amount) }} of {{ currency.format(goal.target_amount) }}
                <span v-if="goal.due_date">&middot; by {{ goal.due_date }}</span>
              </p>
            </div>
            <div class="flex flex-col items-end gap-1">
              <div class="text-sm font-medium text-gray-200">
                {{ Math.round(goal.progress * 100) }}%
              </div>
              <div class="flex gap-1">
                <button
                  type="button"
                  class="text-xs text-emerald-400 hover:text-emerald-300"
                  @click="startEdit(goal)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-xs text-rose-400 hover:text-rose-300"
                  @click="deleteGoal(goal)"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>

          <div class="h-2 w-full rounded-full bg-[#111827] overflow-hidden">
            <div
              class="h-full rounded-full bg-emerald-500"
              :style="{ width: `${Math.round(goal.progress * 100)}%` }"
            ></div>
          </div>
        </div>

        <div v-if="goals.length" class="rounded-lg border border-[#333741] bg-[#020617] p-4">
          <p class="text-xs text-gray-400 uppercase mb-2">Goals Progress Chart</p>
          <div class="flex items-end gap-2 h-28">
            <div
              v-for="goal in chartGoals"
              :key="goal.id"
              class="flex-1 flex flex-col items-center gap-1 min-w-0"
            >
              <div class="w-full h-full rounded-md bg-[#111827] overflow-hidden flex items-end">
                <div
                  class="w-full bg-emerald-500 rounded-md"
                  :style="{ height: `${Math.max(4, Math.round(goal.progress * 100))}%` }"
                ></div>
              </div>
              <p class="text-[10px] text-gray-400 truncate w-full text-center" :title="goal.name">
                {{ goal.name }}
              </p>
            </div>
          </div>
        </div>
      </div>
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
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { useFormatters } from '@/Composables/useFormatters';

interface Goal {
  id: number;
  name: string;
  target_amount: number;
  current_amount: number;
  due_date: string | null;
  progress: number;
}

interface GoalsPageProps {
  goals: Goal[];
}

const page = usePage<GoalsPageProps>();
const goals = page.props.goals ?? [];

const form = useForm({
  name: '',
  target_amount: null as number | null,
  current_amount: null as number | null,
  due_date: '' as string | null,
});

const submit = () => {
  form.post('/goals', {
    onSuccess: () => {
      form.reset('name', 'target_amount', 'current_amount', 'due_date');
    },
  });
};

const { currency } = useFormatters();

const editingGoal = ref<Goal | null>(null);

const editForm = useForm({
  name: '',
  target_amount: null as number | null,
  current_amount: null as number | null,
  due_date: '' as string | null,
});

const startEdit = (goal: Goal) => {
  editingGoal.value = goal;
  editForm.name = goal.name;
  editForm.target_amount = goal.target_amount;
  editForm.current_amount = goal.current_amount;
  editForm.due_date = goal.due_date;
};

const cancelEdit = () => {
  editingGoal.value = null;
  editForm.reset('name', 'target_amount', 'current_amount', 'due_date');
};

const submitEdit = () => {
  if (!editingGoal.value) return;

  editForm.put(`/goals/${editingGoal.value.id}`, {
    onSuccess: () => {
      cancelEdit();
    },
  });
};

const deleteGoal = (goal: Goal) => {
  if (!confirm('Delete this goal?')) return;
  router.delete(`/goals/${goal.id}`);
};

const chartGoals = computed(() => goals.slice(0, 5));
</script>
