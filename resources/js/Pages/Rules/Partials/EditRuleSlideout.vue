<template>
    <div>
        <SlideOut :show="show" @close="close" :max-width="'sm'">
            <template #title>
                <h3 class="font-semibold font-sans text-[#F5F5F6] text-xl">Edit Rule</h3>
            </template>

            <template #body>
                <div class="flex flex-col w-full">
                    <div class="flex flex-col mt-5">
                        <InputLabel value="Text to match"/>
                        <TextInput class="mt-1" v-model="form.search_string" placeholder="Text to match against"/>
                        <InputError class="mt-2" :message="form.errors.search_string" />
                    </div>

                    <div class="flex flex-col mt-5">
                        <InputLabel value="Replace String"/>
                        <TextInput class="mt-1" v-model="form.replace_string" placeholder="Text to replace"/>
                        <InputError class="mt-2" :message="form.errors.replace_string" />
                    </div>

                    <div class="flex flex-col mt-5">
                        <InputLabel value="Category"/>
                        <select v-model="form.category_id" class="mt-1 block w-full rounded-md bg-transparent border border-[#333741] text-[#CECFD2] py-2 px-3">
                            <option value="" disabled>Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.category_id" />
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex items-center justify-end space-x-3 px-3 py-4 border-t border-[#1F242F]">
                    <button type="button" @click="close" class="bg-[#161B26] text-[#CECFD2] cursor-pointer px-4 py-[10px] rounded-lg font-semibold border border-[#333741]">
                        Cancel
                    </button>
                    <button @click="update" class="bg-[#155EEF] text-white cursor-pointer px-4 py-[10px] rounded-lg font-semibold border border-[#155EEF]">
                        Update
                    </button>
                </div>
            </template>
        </SlideOut>
    </div>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SlideOut from '@/Components/SlideOut.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { useEventBus } from '@vueuse/core';
import { ref } from 'vue';

const show = ref(false);
const ruleId = ref(null);
const categories = ref([]);

const form = useForm({
    search_string: '',
    replace_string: '',
    category_id: '',
});

const promptBus = useEventBus('ff-prompt-event-bus');
const notifyBus = useEventBus('ff-notification-event-bus');

const listener = (event, data) => {
    if (event == 'prompt-edit-rule') {
        show.value = true;
        ruleId.value = data.rule.id;
        categories.value = data.categories;
        form.search_string = data.rule.search_string;
        form.replace_string = data.rule.replace_string || '';
        form.category_id = data.rule.category_id;
    }
}
promptBus.on(listener);

const close = () => {
    show.value = false;
    form.reset();
    ruleId.value = null;
}

const update = () => {
    form.put(`/rules/${ruleId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            show.value = false;
            form.reset();
            ruleId.value = null;
            notifyBus.emit('notify', {
                title: 'Rule Updated',
                body: 'Rule has been updated successfully.',
                type: 'success'
            });
        }
    });
}
</script>
