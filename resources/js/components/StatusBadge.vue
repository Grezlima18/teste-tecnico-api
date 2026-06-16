<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const classes = computed(() => {
    switch (props.status) {
        case 'Exame Pronto':
            return 'bg-green-100 text-green-800';
        case 'Enviado ao Apoio':
            return 'bg-amber-100 text-amber-800';
        default:
            return 'bg-gray-100 text-gray-700';
    }
});
</script>

<template>
    <span
        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
        :class="classes"
    >
        {{ status }}
    </span>
</template>
