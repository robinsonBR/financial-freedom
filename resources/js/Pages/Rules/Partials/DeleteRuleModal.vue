<template>
    <Modal @close="closeModal" :show="show" :max-width="'md'">
        <div class="flex flex-col w-full">
            <div class="flex items-center justify-between w-full">
                <TrashModalIcon class="flex-shrink-0"/>
                
                <button type="button" @click="closeModal">
                    <ModalCloseIcon/>
                </button>
            </div>

            <span class="font-sans text-lg font-semibold text-[#F5F5F6] mt-4">Delete Rule</span>
            <span class="font-sans text-sm text-[#94969C]">Are you sure you want to delete this rule? This action cannot be undone.</span>

            <div class="pt-8 flex items-center justify-end">
                <button type="button" @click="closeModal" class="bg-[#161B26] text-[#CECFD2] cursor-pointer px-4 py-[10px] rounded-lg font-semibold border border-[#333741]">
                    Cancel
                </button>
                <button @click="submit" class="ml-3 bg-[#D92D20] text-white cursor-pointer px-4 py-[10px] rounded-lg font-semibold border border-[#D92D20]">
                    Delete
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';
import ModalCloseIcon from '@/Components/Icons/ModalCloseIcon.vue';
import TrashModalIcon from '@/Components/Icons/TrashModalIcon.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useEventBus } from '@vueuse/core';

const promptBus = useEventBus('ff-prompt-event-bus');
const notifyBus = useEventBus('ff-notification-event-bus');
const show = ref(false);
const ruleId = ref(null);

const listener = (event, rule) => {
    if (event == 'prompt-delete-rule') {
        ruleId.value = rule.id;
        show.value = true;
    }
}
promptBus.on(listener);

const closeModal = () => {
    show.value = false;
    ruleId.value = null;
}

const submit = () => {
    router.delete(`/rules/${ruleId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            show.value = false;
            ruleId.value = null;
            notifyBus.emit('notify', {
                title: 'Rule Deleted',
                body: 'Rule has been deleted successfully.',
                type: 'success'
            });
        }
    })
}

</script>
