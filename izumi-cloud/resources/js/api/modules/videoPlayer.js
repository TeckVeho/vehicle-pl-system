import * as RequestApi from '../request';

export function getListVideoPlayer(url, params) {
    return RequestApi.getAll(url, params);
}

export function postFile(url, data) {
    return RequestApi.postOne(url, data);
}

export function postVideo(url, data) {
    return RequestApi.postOne(url, data);
}

export function deleteVideo(url) {
    return RequestApi.deleteOne(url);
}

export function getVideoDetail(url) {
    return RequestApi.getOne(url);
}

export function editVideo(url, data) {
    return RequestApi.putOne(url, data);
}

export function changeVideoOrder(url, data) {
    return RequestApi.putOne(url, data);
}

export function assignMovieOnDates(url, data) {
    return RequestApi.postOne(url, data);
}

export function getMovieOnDates(url) {
    return RequestApi.getAll(url);
}

export function getDeliveryRecord(url) {
    return RequestApi.getAll(url);
}

export function downloadMovies(url) {
    return RequestApi.getAll(url);
}

export function updateMovieLoopEnabled(url, data) {
    return RequestApi.putOne(url, data);
}

export function getListBulkExport(url) {
    return RequestApi.getAll(url);
}
