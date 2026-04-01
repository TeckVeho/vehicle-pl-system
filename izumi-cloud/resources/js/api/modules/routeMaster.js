import * as RequestApi from '../request';

export function getOneRoute(url, params) {
    return RequestApi.get(url, params);
}

export function getListRoute(url, params) {
    return RequestApi.get(url, params);
}

export function getFilterCustomer(url, params) {
    return RequestApi.get(url, params);
}

export function getFilterStore(url, params) {
    return RequestApi.get(url, params);
}

export function getFilterDepartment(url, params) {
    return RequestApi.get(url, params);
}

export function postRoute(url, data) {
    return RequestApi.postOne(url, data);
}

export function postManyRoute(url, data) {
    return RequestApi.postOne(url, data);
}

export function deleteRoute(url) {
    return RequestApi.deleteOne(url);
}

export function importCSV(url, data) {
    return RequestApi.postOne(url, data);
}

export function getListStore(url, params = null) {
    return RequestApi.get(url, params);
}
