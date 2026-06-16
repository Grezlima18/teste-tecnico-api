<script setup>
import { onMounted, onUnmounted } from 'vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
    attendance: {
        type: Object,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

function formatDate(value) {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString('pt-BR');
}

function handleKeydown(event) {
    if (event.key === 'Escape' && props.open) {
        emit('close');
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-black/50"
                @click="emit('close')"
            />

            <div
                class="relative z-10 max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-lg bg-white shadow-xl"
                role="dialog"
                aria-modal="true"
            >
                <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            <template v-if="attendance">
                                Atendimento #{{ attendance.id }}
                            </template>
                            <template v-else>
                                Detalhes do atendimento
                            </template>
                        </h2>
                        <p
                            v-if="attendance?.patient"
                            class="mt-1 text-sm text-gray-600"
                        >
                            {{ attendance.patient.name }} — {{ formatDate(attendance.requested_at) }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-md px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                        @click="emit('close')"
                    >
                        Fechar
                    </button>
                </div>

                <div class="px-6 py-4">
                    <p v-if="loading" class="text-sm text-gray-500">
                        Carregando atendimento...
                    </p>

                    <div v-else-if="attendance" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">
                                        Exame
                                    </th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">
                                        Código
                                    </th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">
                                        Tipo
                                    </th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">
                                        Status
                                    </th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">
                                        Protocolo
                                    </th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">
                                        Resultado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="exam in attendance.exams" :key="exam.id">
                                    <td class="px-3 py-2 text-gray-900">
                                        {{ exam.name }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-700">
                                        {{ exam.code }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="exam.is_external ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'"
                                        >
                                            {{ exam.is_external ? 'Externo' : 'Interno' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <StatusBadge :status="exam.status" />
                                    </td>
                                    <td class="px-3 py-2 text-gray-700">
                                        {{ exam.protocol ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-700">
                                        <template v-if="exam.status === 'Exame Pronto' && exam.result">
                                            {{ exam.result }}
                                        </template>
                                        <template v-else>
                                            —
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
