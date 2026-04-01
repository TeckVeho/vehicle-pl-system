import * as RequestApi from '../request';

export function getListDepartment(url, params) {
    return RequestApi.get(url, params);
}

export function getOneDepartment(url) {
    return RequestApi.getOne(url);
}

export function getLineWorkPIC(url) {
    return RequestApi.getOne(url);
}

export function putDepartment(url, data) {
    return RequestApi.putOne(url, data);
}

export function changeOrder(url, data) {
    return RequestApi.postOne(url, data);
}

export function searchUser(url, data) {
    return RequestApi.getAll(url, data);
}

export function getEmployeeAll(url) {
    return RequestApi.get(url);
}
