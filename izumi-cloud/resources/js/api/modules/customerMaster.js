import * as RequestApi from '../request';

export function getListCustomer(url, params) {
    return RequestApi.get(url, params);
}

export function postCustomer(url, data) {
    return RequestApi.postOne(url, data);
}

export function getOneCustomer(url) {
    return RequestApi.getOne(url);
}

export function putCustomer(url, data) {
    return RequestApi.putOne(url, data);
}

export function deleteCustomer(url) {
    return RequestApi.deleteOne(url);
}
