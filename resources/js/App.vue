<script setup>
import { ref } from 'vue';
import AttendanceSection from './components/AttendanceSection.vue';
import PatientsSection from './components/PatientsSection.vue';

const tabs = [
    { id: 'patients', label: 'Pacientes' },
    { id: 'attendance', label: 'Atendimento' },
];

const activeTab = ref('attendance');
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-5xl px-4 py-6">
                <h1 class="text-2xl font-semibold text-gray-900">
                    Sistema de Laboratório
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Cadastro de pacientes e atendimentos
                </p>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8">
            <nav class="mb-6 flex gap-2 border-b border-gray-200">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    class="border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="activeTab === tab.id
                        ? 'border-blue-600 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700'"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </nav>

            <PatientsSection v-if="activeTab === 'patients'" />
            <AttendanceSection v-else />
        </main>
    </div>
</template>
