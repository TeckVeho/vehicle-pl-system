import * as RequestApi from '../request';

export function getDataList(url) {
    return RequestApi.getAll(url);
}

export function getDataListDetail(url) {
    return RequestApi.getOne(url);
}

export function getFile(url) {
    return RequestApi.getOne(url);
}
