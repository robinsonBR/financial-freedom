<template>
    <Modal @close="closeModal" :show="show" :max-width="'md'">
        <form class="flex flex-col w-full" @submit.prevent="submit">
            <div class="flex items-center justify-between w-full">
                <FolderModalIcon class="flex-shrink-0"/>
                
                <button type="button" @click="closeModal">
                    <ModalCloseIcon/>
                </button>
            </div>

            <span class="font-sans text-lg font-semibold text-[#F5F5F6] mt-4">Edit Monthly Budget</span>
            <span class="font-sans text-sm text-[#94969C]">Update the monthly budget for {{ categoryName }}</span>

            <div class="flex flex-col mt-5">
                <InputLabel value="Monthly Budget"/>
                <TextInput v-model="form.monthly_budget" class="mt-1" type="number" min="0" step="0.01" />
                <InputError class="mt-2" :message="form.errors.monthly_budget" />
            </div>

            <div class="pt-8 flex items-center justify-end">
                <button type="button" @click="closeModal" class="bg-[#161B26] text-[#CECFD2] cursor-pointer px-4 py-[10px] rounded-lg font-semibold border border-[#333741]">
                    Cancel
                </button>
                <button class="ml-3 bg-[#155EEF] text-white cursor-pointer px-4 py-[10px] rounded-lg font-semibold border border-[#155EEF]">
                    Update
                </button>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';
import ModalCloseIcon from '@/Components/Icons/ModalCloseIcon.vue';
import FolderModalIcon from '@/Components/Icons/FolderModalIcon.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useEventBus } from '@vueuse/core';

const promptBus = useEventBus('ff-prompt-event-bus');
const notifyBus = useEventBus('ff-notification-event-bus');
const show = ref(false);
const categoryId = ref(null);
const categoryName = ref('');

const form = useForm({
    monthly_budget: 0,
});

const listener = (event, category) => {
    if (event == 'prompt-edit-budget') {
        categoryId.value = category.categoryId;
        categoryName.value = category.name;
        form.monthly_budget = category.planned;
        show.value = true;
    }
}
promptBus.on(listener);

const closeModal = () => {
    form.clearErrors();
    form.reset();
    show.value = false;
    categoryId.value = null;
    categoryName.value = '';
}

const submit = () => {
    form.put(`/settings/categories/${categoryId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            show.value = false;
            form.reset();
            categoryId.value = null;
            categoryName.value = '';
            notifyBus.emit('notify', {
                title: 'Budget Updated',
                body: 'Monthly budget has been updated successfully.',
                type: 'success'
            });
        }
    })
}

</script>
