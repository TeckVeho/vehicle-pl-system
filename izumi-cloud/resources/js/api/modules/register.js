import * as RequestApi from '../request';

export function postRegister(url, data) {
    return RequestApi.postOne(url, data);
}
