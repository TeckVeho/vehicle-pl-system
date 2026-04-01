import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

import dev from './modules/dev';
import login from './modules/login';
import CONST_ROLE from '@/const/role';
import register from './modules/register';
import playlist from './modules/playlist';
import errorPage from './modules/errorPage';
import authMobile from './modules/authMobile';
import previewPDF from './modules/previewPDF';
import dataManager from './modules/dataManager';
import videoPlayer from './modules/videoPlayer';
import notification from './modules/notification';
import playRecorder from './modules/playRecorder';
import masterManager from './modules/masterManager';
import resetPassword from './modules/reset-password';
import transportation from './modules/transportation';
import driverRecorder from './modules/driverRecorder';
import publicPlaylist from './modules/publicPlaylist';
import publicPlayRecorder from './modules/publicPlayRecorder';
import publicDriverRecorder from './modules/publicDriverRecorder';
import publicDriverRecorderDetail from './modules/publicDriverRecorderDetail';

export const constantRoutes = [
    dev,
    login,
    register,
    previewPDF,
    authMobile,
    notification,
    resetPassword,
    publicPlaylist,
    publicPlayRecorder,
    publicDriverRecorder,
    publicDriverRecorderDetail,
];

export const asyncRoutes = [
    transportation,
    dataManager,
    masterManager,
    driverRecorder,
    playRecorder,
    playlist,
    videoPlayer,
    {
        path: '/',
        redirect: { name: 'TransportationIndex' },
        hidden: true,
        meta: {
            roles: [
                CONST_ROLE.CREW,
                CONST_ROLE.CLERKS,
                CONST_ROLE.TL,
                CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                CONST_ROLE.ACCOUNTING,
                CONST_ROLE.GENERAL_AFFAIRS,
                CONST_ROLE.PERSONNEL_LABOR,
                CONST_ROLE.AM_SM,
                CONST_ROLE.DIRECTOR,
                CONST_ROLE.DX_MANAGER,
            ],
        },
    },
    errorPage,
    {
        path: '*',
        redirect: { name: 'ErrorPages' },
        hidden: true,
    },
];

const createRouter = () => new VueRouter({
    mode: 'history',
    scrollBehavior: () => ({ y: 0 }),
    routes: constantRoutes,
});

const router = createRouter();

export function resetRouter() {
    const newRouter = createRouter();
    router.matcher = newRouter.matcher;
}

export default router;

