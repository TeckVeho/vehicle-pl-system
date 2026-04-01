import * as RequestApi from '../request';

export function getListUrgentContact(url, params) {
    return RequestApi.getAll(url, params);
}
