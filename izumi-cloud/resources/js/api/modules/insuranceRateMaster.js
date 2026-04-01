import * as RequestApi from '../request';

export function getInsuranceRateList(url) {
    return RequestApi.getAll(url);
}

export function getInsuranceRateHistory(url) {
    return RequestApi.getAll(url);
}

export function getInsuranceRateDetail(url) {
    return RequestApi.getOne(url);
}

export function putInsuranceRate(url, data) {
    return RequestApi.putOne(url, data);
}
