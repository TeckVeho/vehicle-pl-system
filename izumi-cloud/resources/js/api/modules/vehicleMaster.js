import * as RequestApi from '../request';

export function getListDepartment(url, params) {
    return RequestApi.getAll(url, params);
}

export function getListVehicle(url, params) {
    return RequestApi.getAll(url, params);
}

export function getDetailVehicle(url, params) {
    return RequestApi.get(url, params);
}

export function createVehicle(url, data) {
    return RequestApi.postOne(url, data);
}

export function updateVehicle(url, data) {
    return RequestApi.putOne(url, data);
}

export function deleteVehicle(url) {
    return RequestApi.deleteOne(url);
}

export function getUserColumnSetting(url, params) {
    return RequestApi.get(url, params);
}

export function postUserColumnSetting(url, data) {
    return RequestApi.postOne(url, data);
}

export function exportCSV(url, data) {
    return RequestApi.getOne(url, data);
}

export function getVehicleDashboard(url, params) {
    return RequestApi.get(url, params);
}

export function getDepartmentByDivision(url, params) {
    return RequestApi.getAll(url, params);
}
