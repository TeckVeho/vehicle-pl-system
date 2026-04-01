import CONST_ROLE from '@/const/role';

const playRecorder = {
    path: '/play-recorder/:id/:idx',
    name: 'PlayRecorder',
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
            CONST_ROLE.DX_USER,
            CONST_ROLE.DX_MANAGER,
            CONST_ROLE.DEPARTMENT_MANAGER,
            CONST_ROLE.QUALITY_CONTROL,
        ],
    },
    // hidden: true,
    component: () => import(/* webpackChunkName: "Play Recoder" */ '@/pages/PlayRecorder/index.vue'),
};

export default playRecorder;
