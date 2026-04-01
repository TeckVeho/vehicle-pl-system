import * as RequestApi from '../request';

export function postResetPassword(url, data) {
    return RequestApi.postOne(url, data);
}
