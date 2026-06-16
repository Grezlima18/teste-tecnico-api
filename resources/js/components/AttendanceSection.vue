<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { createAttendance, getAttendance, getAttendances, getExamTypes, getPatient, getPatients } from '../api';
import AttendanceDetailModal from './AttendanceDetailModal.vue';

const searchName = ref('');
const searchId = ref('');
const searchResults = ref([]);
const selectedPatient = ref(null);
const examTypes = ref([]);
const selectedExamIds = ref([]);
const modalAttendance = ref(null);
const attendances = ref([]);
const loadingAttendances = ref(false);
const loadingDetail = ref(false);
const loadingExams = ref(false);
const loadingSearch = ref(false);
const loadingSubmit = ref(false);
const errorMessage = ref('');
const infoMessage = ref('');

let searchTimeout = null;

const canSubmit = computed(() => {
    return selectedPatient.value !== null && selectedExamIds.value.length > 0 && !loadingSubmit.value;
});

const isModalOpen = computed(() => modalAttendance.value !== null || loadingDetail.value);

function openModal(data) {
    modalAttendance.value = data;
}

function closeModal() {
    modalAttendance.value = null;
}

async function loadAttendances() {
    loadingAttendances.value = true;

    try {
        const response = await getAttendances();
        attendances.value = response.data;
    } catch (error) {
        errorMessage.value = error.message;
    } finally {
        loadingAttendances.value = false;
    }
}

async function viewAttendance(id) {
    loadingDetail.value = true;
    openModal(null);

    try {
        const response = await getAttendance(id);
        openModal(response.data);
    } catch (error) {
        errorMessage.value = error.message;
        closeModal();
    } finally {
        loadingDetail.value = false;
    }
}

function formatDate(value) {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString('pt-BR');
}

async function loadExamTypes() {
    loadingExams.value = true;

    try {
        const response = await getExamTypes();
        examTypes.value = response.data;
    } catch (error) {
        errorMessage.value = error.message;
    } finally {
        loadingExams.value = false;
    }
}

async function searchByName() {
    if (!searchName.value.trim()) {
        searchResults.value = [];
        return;
    }

    loadingSearch.value = true;
    errorMessage.value = '';

    try {
        const response = await getPatients(searchName.value.trim());
        searchResults.value = response.data;
    } catch (error) {
        errorMessage.value = error.message;
        searchResults.value = [];
    } finally {
        loadingSearch.value = false;
    }
}

async function searchById() {
    if (!searchId.value) {
        return;
    }

    loadingSearch.value = true;
    errorMessage.value = '';
    searchResults.value = [];

    try {
        const response = await getPatient(searchId.value);
        selectedPatient.value = response.data;
        searchName.value = '';
    } catch (error) {
        errorMessage.value = error.message;
        selectedPatient.value = null;
    } finally {
        loadingSearch.value = false;
    }
}

function selectPatient(patient) {
    selectedPatient.value = patient;
    searchResults.value = [];
    searchName.value = patient.name;
    searchId.value = String(patient.id);
}

function toggleExam(examId) {
    const index = selectedExamIds.value.indexOf(examId);

    if (index === -1) {
        selectedExamIds.value.push(examId);
        return;
    }

    selectedExamIds.value.splice(index, 1);
}

function isExamSelected(examId) {
    return selectedExamIds.value.includes(examId);
}

async function handleSubmit() {
    if (!canSubmit.value) {
        return;
    }

    loadingSubmit.value = true;
    errorMessage.value = '';
    infoMessage.value = '';

    try {
        const response = await createAttendance({
            patient_id: selectedPatient.value.id,
            exam_ids: selectedExamIds.value,
        });

        openModal(response.data);

        const hasSentToLab = response.data.exams.some(
            (exam) => exam.status === 'Enviado ao Apoio',
        );

        if (hasSentToLab) {
            infoMessage.value = 'Alguns exames externos ficaram com status "Enviado ao Apoio" devido à instabilidade da API do laboratório. Tente novamente mais tarde.';
        }

        await loadAttendances();
    } catch (error) {
        const validationErrors = Object.values(error.errors ?? {}).flat();

        errorMessage.value = validationErrors.length
            ? validationErrors.join(' ')
            : error.message;
    } finally {
        loadingSubmit.value = false;
    }
}

watch(searchName, () => {
    clearTimeout(searchTimeout);

    if (!searchName.value.trim()) {
        searchResults.value = [];
        return;
    }

    searchTimeout = setTimeout(searchByName, 300);
});

onMounted(() => {
    loadExamTypes();
    loadAttendances();
});
</script>

<template>
    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">
                Selecionar paciente
            </h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700" for="search-name">
                        Buscar por nome
                    </label>
                    <input
                        id="search-name"
                        v-model="searchName"
                        type="text"
                        placeholder="Digite o nome do paciente"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700" for="search-id">
                        Buscar por ID
                    </label>
                    <div class="flex gap-2">
                        <input
                            id="search-id"
                            v-model="searchId"
                            type="number"
                            min="1"
                            placeholder="ID"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            @click="searchById"
                        >
                            Buscar
                        </button>
                    </div>
                </div>
            </div>

            <p v-if="loadingSearch" class="mt-3 text-sm text-gray-500">
                Buscando...
            </p>

            <ul
                v-if="searchResults.length > 0"
                class="mt-3 divide-y divide-gray-100 rounded-md border border-gray-200"
            >
                <li
                    v-for="patient in searchResults"
                    :key="patient.id"
                    class="cursor-pointer px-4 py-3 text-sm hover:bg-gray-50"
                    @click="selectPatient(patient)"
                >
                    <span class="font-medium text-gray-900">{{ patient.name }}</span>
                    <span class="ml-2 text-gray-500">ID {{ patient.id }}</span>
                </li>
            </ul>

            <div
                v-if="selectedPatient"
                class="mt-4 rounded-md border border-blue-200 bg-blue-50 p-4 text-sm"
            >
                <p class="font-medium text-blue-900">
                    Paciente selecionado
                </p>
                <p class="mt-1 text-blue-800">
                    {{ selectedPatient.name }} — ID {{ selectedPatient.id }} —
                    {{ selectedPatient.sex === 'm' ? 'Masculino' : 'Feminino' }} —
                    {{ selectedPatient.birth_date }}
                </p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">
                Exames do atendimento
            </h2>

            <p v-if="loadingExams" class="text-sm text-gray-500">
                Carregando exames...
            </p>

            <div v-else-if="examTypes.length === 0" class="text-sm text-gray-500">
                Nenhum exame disponível. Execute <code class="rounded bg-gray-100 px-1">php artisan db:seed</code> para popular os exames.
            </div>

            <div v-else class="space-y-2">
                <label
                    v-for="exam in examTypes"
                    :key="exam.id"
                    class="flex cursor-pointer items-center gap-3 rounded-md border border-gray-200 px-4 py-3 hover:bg-gray-50"
                >
                    <input
                        type="checkbox"
                        :checked="isExamSelected(exam.id)"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        @change="toggleExam(exam.id)"
                    >
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-900">{{ exam.name }}</span>
                        <span class="ml-2 text-sm text-gray-500">{{ exam.code }}</span>
                    </div>
                    <span
                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="exam.is_external ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'"
                    >
                        {{ exam.is_external ? 'Externo' : 'Interno' }}
                    </span>
                </label>
            </div>

            <button
                type="button"
                :disabled="!canSubmit"
                class="mt-4 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                @click="handleSubmit"
            >
                {{ loadingSubmit ? 'Criando atendimento...' : 'Criar atendimento' }}
            </button>
        </div>

        <p v-if="infoMessage" class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            {{ infoMessage }}
        </p>

        <p v-if="errorMessage" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ errorMessage }}
        </p>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    Atendimentos realizados
                </h2>
                <button
                    type="button"
                    class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                    :disabled="loadingAttendances"
                    @click="loadAttendances"
                >
                    {{ loadingAttendances ? 'Atualizando...' : 'Atualizar' }}
                </button>
            </div>

            <p v-if="loadingAttendances" class="text-sm text-gray-500">
                Carregando atendimentos...
            </p>

            <div v-else-if="attendances.length === 0" class="text-sm text-gray-500">
                Nenhum atendimento realizado ainda.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-500">
                                ID
                            </th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500">
                                Paciente
                            </th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500">
                                Data
                            </th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500">
                                Exames
                            </th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500">
                                Ação
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="item in attendances"
                            :key="item.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-3 py-2 text-gray-900">
                                {{ item.id }}
                            </td>
                            <td class="px-3 py-2 text-gray-900">
                                {{ item.patient?.name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-gray-700">
                                {{ formatDate(item.requested_at) }}
                            </td>
                            <td class="px-3 py-2 text-gray-700">
                                {{ item.exams.length }}
                            </td>
                            <td class="px-3 py-2">
                                <button
                                    type="button"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-800"
                                    :disabled="loadingDetail"
                                    @click="viewAttendance(item.id)"
                                >
                                    Ver detalhes
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AttendanceDetailModal
            :open="isModalOpen"
            :attendance="modalAttendance"
            :loading="loadingDetail"
            @close="closeModal"
        />
    </div>
</template>
