import * as RequestApi from '../request';

export function uploadData(url, data) {
    return RequestApi.postOne(url, data);
}
