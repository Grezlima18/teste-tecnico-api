<script setup>
import { onMounted, ref } from 'vue';
import { createPatient, getPatients } from '../api';

const form = ref({
    name: '',
    sex: '',
    birth_date: '',
});

const patients = ref([]);
const loading = ref(false);
const loadingList = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

async function loadPatients() {
    loadingList.value = true;

    try {
        const response = await getPatients();
        patients.value = response.data;
    } catch (error) {
        errorMessage.value = error.message;
    } finally {
        loadingList.value = false;
    }
}

async function handleSubmit() {
    loading.value = true;
    successMessage.value = '';
    errorMessage.value = '';

    try {
        await createPatient(form.value);
        successMessage.value = 'Paciente cadastrado com sucesso.';
        form.value = { name: '', sex: '', birth_date: '' };
        await loadPatients();
    } catch (error) {
        const validationErrors = Object.values(error.errors ?? {}).flat();

        errorMessage.value = validationErrors.length
            ? validationErrors.join(' ')
            : error.message;
    } finally {
        loading.value = false;
    }
}

onMounted(loadPatients);
</script>

<template>
    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">
                Cadastrar paciente
            </h2>

            <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="handleSubmit">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700" for="patient-name">
                        Nome
                    </label>
                    <input
                        id="patient-name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700" for="patient-sex">
                        Sexo
                    </label>
                    <select
                        id="patient-sex"
                        v-model="form.sex"
                        required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="" disabled>
                            Selecione
                        </option>
                        <option value="m">
                            Masculino
                        </option>
                        <option value="f">
                            Feminino
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700" for="patient-birth-date">
                        Data de nascimento
                    </label>
                    <input
                        id="patient-birth-date"
                        v-model="form.birth_date"
                        type="date"
                        required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                </div>

                <div class="sm:col-span-2">
                    <button
                        type="submit"
                        :disabled="loading"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ loading ? 'Salvando...' : 'Salvar paciente' }}
                    </button>
                </div>
            </form>

            <p v-if="successMessage" class="mt-4 text-sm text-green-600">
                {{ successMessage }}
            </p>
            <p v-if="errorMessage" class="mt-4 text-sm text-red-600">
                {{ errorMessage }}
            </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">
                Pacientes cadastrados
            </h2>

            <p v-if="loadingList" class="text-sm text-gray-500">
                Carregando...
            </p>

            <div v-else-if="patients.length === 0" class="text-sm text-gray-500">
                Nenhum paciente cadastrado.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-500">
                                ID
                            </th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500">
                                Nome
                            </th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500">
                                Sexo
                            </th>
                            <th class="px-3 py-2 text-left font-medium text-gray-500">
                                Nascimento
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="patient in patients" :key="patient.id">
                            <td class="px-3 py-2 text-gray-900">
                                {{ patient.id }}
                            </td>
                            <td class="px-3 py-2 text-gray-900">
                                {{ patient.name }}
                            </td>
                            <td class="px-3 py-2 text-gray-700">
                                {{ patient.sex === 'm' ? 'Masculino' : 'Feminino' }}
                            </td>
                            <td class="px-3 py-2 text-gray-700">
                                {{ patient.birth_date }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
