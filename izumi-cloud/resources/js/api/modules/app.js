import * as RequestApi from '../request';

export function postRefreshToken(url, data) {
    return RequestApi.postOne(url, data);
}
