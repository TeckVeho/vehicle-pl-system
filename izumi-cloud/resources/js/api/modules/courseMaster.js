import * as RequestApi from '../request';

export function getListDepartment(url, params) {
    return RequestApi.getAll(url, params);
}

export function getListRoute(url, params) {
    return RequestApi.get(url, params);
}

export function postCourse(url, data) {
    return RequestApi.postOne(url, data);
}

export function getListCourse(url, params) {
    return RequestApi.get(url, params);
}

export function deleteCourse(url) {
    return RequestApi.deleteOne(url);
}

export function getCourse(url) {
    return RequestApi.getOne(url);
}

export function putCourse(url, data) {
    return RequestApi.putOne(url, data);
}
