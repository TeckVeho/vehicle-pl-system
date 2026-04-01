import * as RequestApi from '../request';

export function getListFiles(url) {
    return RequestApi.getAll(url);
}

export function postFiles(url, data) {
    return RequestApi.postOne(url, data);
}

export function deleteFiles(url) {
    return RequestApi.deleteOne(url);
}

export function changeOrder(url, data) {
    return RequestApi.postOne(url, data);
}
