async function request(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...options.headers,
        },
        ...options,
    });

    const body = await response.json().catch(() => ({}));

    if (!response.ok) {
        const error = new Error(body.message ?? 'Erro na requisição.');
        error.status = response.status;
        error.errors = body.errors ?? {};
        throw error;
    }

    return body;
}

export function getPatients(name) {
    const params = name ? `?name=${encodeURIComponent(name)}` : '';

    return request(`/api/patients${params}`);
}

export function getPatient(id) {
    return request(`/api/patients/${id}`);
}

export function createPatient(data) {
    return request('/api/patients', {
        method: 'POST',
        body: JSON.stringify(data),
    });
}

export function getExamTypes() {
    return request('/api/exam-types');
}

export function createAttendance(data) {
    return request('/api/attendances', {
        method: 'POST',
        body: JSON.stringify(data),
    });
}

export function getAttendances() {
    return request('/api/attendances');
}

export function getAttendance(id) {
    return request(`/api/attendances/${id}`);
}
