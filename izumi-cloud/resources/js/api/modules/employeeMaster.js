import * as RequestApi from '../request';

export function getList(url, params) {
    return RequestApi.get(url, params);
}

export function getListDepartment(url, params) {
    return RequestApi.getAll(url, params);
}

export function getDetailEmployee(url, params) {
    return RequestApi.get(url, params);
}

export function getDepartmentWorking(url, params) {
    return RequestApi.get(url, params);
}

export function postEmployee(url, data) {
    return RequestApi.putOne(url, data);
}

export function getListCourse(url, params) {
    return RequestApi.get(url, params);
}

export function updateEquipmentData(url, data) {
    return RequestApi.putOne(url, data);
}

export function postPDF(url, data) {
    return RequestApi.postOne(url, data);
}

export function updateDriverLicense(url, data) {
    return RequestApi.postOne(url, data);
}

export function updateDrivingRecordCertificate(url, data) {
    return RequestApi.postOne(url, data);
}

export function updateAptitudeAssessmentForm(url, data) {
    return RequestApi.postOne(url, data);
}

export function updateHealthExaminationResults(url, data) {
    return RequestApi.postOne(url, data);
}

export function importCSV(url, data) {
    return RequestApi.postOne(url, data);
}

export function postPDFEmployeeDetail(url, data) {
    return RequestApi.postOne(url, data);
}
export function deletePDF(url, data) {
    return RequestApi.deleteOne(url, data);
}

export function deleteHealthExamPDF(url, data) {
    return RequestApi.deleteOne(url, data);
}

export function deleteDriverLicensePDF(url, data) {
    return RequestApi.deleteOne(url, data);
}

export function deleteDrivingRecord(url, data) {
    return RequestApi.deleteOne(url, data);
}

export function deleteAptitude(url, data) {
    return RequestApi.deleteOne(url, data);
}
