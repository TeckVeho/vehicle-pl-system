import * as RequestApi from '../request';

export function getUserList(url) {
    return RequestApi.getAll(url);
}

export function getRoleList(url) {
    return RequestApi.getAll(url);
}

export function getOneUser(url) {
    return RequestApi.getOne(url);
}

export function removeUser(url, user_id) {
    return RequestApi.deleteOne(url, user_id);
}

export function createUser(url, data) {
    return RequestApi.postOne(url, data);
}

export function updateUser(url, data) {
    return RequestApi.putOne(url, data);
}

export function sendMailSetUpPassword(url, data) {
    return RequestApi.get(url, data);
}

export function getListUserByDepartment(url) {
    return RequestApi.getAll(url);
}
