<template>
  <div class="rounded-lg border border-[#333741] bg-[#020617] p-4">
    <p class="text-xs text-gray-400 uppercase mb-1">{{ label }}</p>
    <p :class="['font-semibold', valueClass]">
      <slot name="value">{{ value }}</slot>
    </p>
    <p v-if="$slots.subtitle" class="mt-1 text-xs text-gray-400">
      <slot name="subtitle" />
    </p>
  </div>
</template>

<script setup lang="ts">
interface Props {
  label: string;
  value?: string;
  valueClass?: string;
}

withDefaults(defineProps<Props>(), {
  value: '',
  valueClass: 'text-lg text-gray-100',
});
</script>
