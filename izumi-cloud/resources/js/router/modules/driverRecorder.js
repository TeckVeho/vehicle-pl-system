import Layout from '@/layout';
import CONST_ROLE from '@/const/role';

const driverRecorder = {
    path: '/driver-recorder',
    name: 'DriverRecorder',
    meta: {
        title: 'ROUTER_DRIVER_RECORDER',
        icon: 'fas fa-camcorder',
    },
    component: Layout,
    redirect: {
        name: 'DriverRecorderList',
    },
    children: [
        {
            path: 'list',
            name: 'DriverRecorderList',
            meta: {
                title: 'ROUTER_DRIVER_RECONRDER_LIST',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "DriverRecorderList" */ '@/pages/DriverRecorder/List/index.vue'),
        },
        {
            path: 'create',
            name: 'DriverRecorderCreate',
            meta: {
                title: 'ROUTER_DRIVER_RECONRDER_LIST',
                roles: [
                    CONST_ROLE.CLERKS,
                    CONST_ROLE.TL,
                    CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                    CONST_ROLE.QUALITY_CONTROL,
                    CONST_ROLE.SITE_MANAGER,
                    CONST_ROLE.HQ_MANAGER,
                    CONST_ROLE.DEPARTMENT_MANAGER,
                    CONST_ROLE.EXECUTIVE_OFFICER,
                    CONST_ROLE.DIRECTOR,
                    CONST_ROLE.DX_USER,
                    CONST_ROLE.DX_MANAGER,
                ],
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "DriverRecorderUpload" */ '@/pages/DriverRecorder/Create/index.vue'),
        },
        {
            path: 'detail/:id',
            name: 'DriverRecorderDetail',
            meta: {
                title: 'ROUTER_DRIVER_RECONRDER_LIST',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "DriverRecorderDetail" */ '@/pages/DriverRecorder/Detail/index.vue'),
        },
        {
            path: 'edit/:id',
            name: 'DriverRecorderEdit',
            meta: {
                title: 'ROUTER_DRIVER_RECONRDER_LIST',
                roles: [
                    CONST_ROLE.CLERKS,
                    CONST_ROLE.TL,
                    CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                    CONST_ROLE.QUALITY_CONTROL,
                    CONST_ROLE.SITE_MANAGER,
                    CONST_ROLE.HQ_MANAGER,
                    CONST_ROLE.DEPARTMENT_MANAGER,
                    CONST_ROLE.EXECUTIVE_OFFICER,
                    CONST_ROLE.DIRECTOR,
                    CONST_ROLE.DX_USER,
                    CONST_ROLE.DX_MANAGER,
                ],
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "DriverRecorderEdit" */ '@/pages/DriverRecorder/Edit/index.vue'),
        },
    ],
};

export default driverRecorder;
