import * as RequestApi from '../request';

export function getDataConnection(url) {
    return RequestApi.getAll(url);
}

export function getDetailDataConnection(url) {
    return RequestApi.getOne(url);
}

export function execQueue(url) {
    return RequestApi.getOne(url);
}

export function getFile(url) {
    return RequestApi.getOne(url);
}
