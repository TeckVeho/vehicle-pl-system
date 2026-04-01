import * as RequestApi from '../request';

const BASE = 'inspection-notification-recipients';

export function getCandidates() {
    return RequestApi.get(`${BASE}/candidates`);
}

export function getList(params = {}) {
    return RequestApi.get(BASE, params);
}

export function save(recipients) {
    return RequestApi.putOne(BASE, { recipients });
}
