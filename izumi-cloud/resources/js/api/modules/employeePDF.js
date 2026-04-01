import * as RequestApi from '../request';

export function getListEmployeePDF(url, params) {
    return RequestApi.get(url, params);
}

export function getDetailEmployeePDF(url, params) {
    return RequestApi.getAll(url, params);
}

export function postEmployeePDF(url, data) {
    return RequestApi.postOne(url, data);
}

export function postEmployeePDFDetail(url, data) {
    return RequestApi.postOne(url, data);
}

export function deleteEmployeePDF(url, data) {
    return RequestApi.deleteOne(url, data);
}

export function postDriverLicense(url, data) {
    return RequestApi.postOne(url, data);
}

export function postDrivingRecord(url, data) {
    return RequestApi.postOne(url, data);
}

export function postAptitude(url, data) {
    return RequestApi.postOne(url, data);
}

export function postHealthExamination(url, data) {
    return RequestApi.postOne(url, data);
}

export function getEmployeeByDepartment(url, data) {
    return RequestApi.getOne(url, data);
}
