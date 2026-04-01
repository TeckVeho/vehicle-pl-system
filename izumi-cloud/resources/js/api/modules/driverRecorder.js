import * as RequestApi from '../request';

export function getListDepartment(url, params) {
    return RequestApi.getAll(url, params);
}

export function getListFile(url, params) {
    return RequestApi.getAll(url, params);
}

export function getDetailFile(url, params) {
    return RequestApi.get(url, params);
}

export function getMoisacVideo(url, params) {
    return RequestApi.get(url, params);
}

export function postUploadData(url, data) {
    return RequestApi.postOne(url, data);
}

export function putUploadData(url, data) {
    return RequestApi.putOne(url, data);
}

export function deleteFile(url) {
    return RequestApi.deleteOne(url);
}

export function downloadFile(url) {
    return RequestApi.getOne(url);
}

export function changeOrder(url, data) {
    return RequestApi.putOne(url, data);
}
