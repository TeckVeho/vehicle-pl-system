import axios from 'axios';
import i18n from '@/lang';
import store from '@/store';
import router from '@/router';
import CONST_AUTH from '@/const/auth';

import { MakeToast } from '@/utils/MakeToast';
import { getToken } from '@/utils/handleToken';
// import { getProfile } from '@/utils/handleProfile';
import { getLanguage } from '@/lang/helper/getLang';
// import { postRefreshToken } from '@/api/modules/app';

const baseURL = process.env.MIX_BASE_API;

const service = axios.create({
    baseURL: baseURL,
    timeout: 100000,
});

// Flag to prevent multiple refresh token requests
// let isRefreshing = false;

service.interceptors.request.use(
    async(config) => {
        const token = getToken();
        // const profile = getProfile();

        config.headers['Accept-Language'] = getLanguage();

        if (token) {
            config.headers['Authorization'] = token;

            // const tokenExpire = profile.expToken;
            // const currentTime = Math.floor(Date.now() / 1000);

            // if (tokenExpire && currentTime >= tokenExpire - 600 && !isRefreshing) {
            //     try {
            //         isRefreshing = true;

            //         const url = '/auth/refresh';

            //         const body = {
            //             access_token: token,
            //         };

            //         const response = await postRefreshToken(url, body);

            //         const { code, data } = response;

            //         if (code === 200) {
            //             const newToken = `Bearer ${data.access_token}`;
            //             const roles = profile.roles;
            //             const expToken = data.exp_token;

            //             const USER = {
            //                 roles: roles || [],
            //                 id: profile.id || '',
            //                 expToken: expToken || '',
            //                 uuid: profile.uuid || '',
            //                 name: profile.name || '',
            //                 role: profile.role || '',
            //                 email: profile.email || '',
            //                 department: profile.department || '',
            //                 department_code: profile.department_code || '',
            //                 supervisor_email: profile.supervisor_email || '',
            //             };

            //             const commit = {
            //                 USER,
            //                 TOKEN: newToken,
            //             };

            //             await store.dispatch('user/saveLogin', commit);

            //             config.headers['Authorization'] = newToken;
            //         }
            //     } catch (error) {
            //         console.error('[40] -> [refreshToken] ==>', error);
            //     } finally {
            //         isRefreshing = false;
            //     }
            // }
        } else {
            if (router.currentRoute.path === '/register' || router.currentRoute.path === '/reset-password') {
                if (router.currentRoute.path !== '/notification') {
                    router.push({ path: '/notification' }).catch(() => {});
                }
            } else if (router.currentRoute.path !== '/login') {
                router.push({ path: '/login' }).catch(() => {});
            }
        }

        return config;
    },
    (error) => {
        Promise.reject(error);
    }
);

service.interceptors.response.use(
    (response) => {
        const USER_NOT_FOUND = CONST_AUTH.USER_NOT_FOUND;

        if (JSON.stringify(USER_NOT_FOUND) === JSON.stringify(response.data)) {
            store.dispatch('user/doLogout')
                .then(() => {
                    router.push('/login');
                })
                .catch(() => {
                    MakeToast({
                        variant: 'danger',
                        title: i18n.$t('DANGER'),
                        content: i18n.$t('TOAST_HAVE_ERROR'),
                    });
                });
        }

        return response.data;
    },
    (error) => {
        const TOKEN_EXPIRE = CONST_AUTH.TOKEN_EXPIRE;

        if (TOKEN_EXPIRE.code === error.response.data.code && TOKEN_EXPIRE.message_content === error.response.data.message_content) {
            store.dispatch('user/doLogout')
                .then(() => {
                    router.push('/login').catch(() => {});
                });
        } else {
            try {
                MakeToast({
                    variant: 'danger',
                    title: i18n.t('DANGER'),
                    content: error.response?.data?.message || i18n.t('TOAST_HAVE_ERROR'),
                });
            } catch (e) {
                // JSDOM teardown / môi trường không có document
            }
        }

        return Promise.reject(error);
    }
);

export { service };
