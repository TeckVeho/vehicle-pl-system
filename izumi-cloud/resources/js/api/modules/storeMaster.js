import * as RequestApi from '../request';

export function getListStore(url, params) {
    return RequestApi.get(url, params);
}

export function postStore(url, data) {
    return RequestApi.postOne(url, data);
}

export function getOneStore(url) {
    return RequestApi.getOne(url);
}

export function updateStore(url, data) {
    return RequestApi.postOne(url, data);
}

export function deleteStore(url) {
    return RequestApi.deleteOne(url);
}
