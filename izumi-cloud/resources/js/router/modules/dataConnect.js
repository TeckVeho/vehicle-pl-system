import Layout from '@/layout';
import CONST_ROLE from '@/const/role';

const dataConnect = {
    path: '/data-connect',
    name: 'DataConnect',
    meta: {
        title: 'ROUTER_DATA_CONNECT',
        icon: 'fas fa-repeat-alt',
        roles: [
            CONST_ROLE.ACCOUNTING,
            CONST_ROLE.GENERAL_AFFAIRS,
            CONST_ROLE.PERSONNEL_LABOR,
            CONST_ROLE.DIRECTOR,
            CONST_ROLE.DX_USER,
            CONST_ROLE.DX_MANAGER,
            CONST_ROLE.HQ_MANAGER,
            CONST_ROLE.DEPARTMENT_MANAGER,
            CONST_ROLE.EXECUTIVE_OFFICER,
            CONST_ROLE.DIRECTOR,
        ],
    },
    component: Layout,
    redirect: { name: 'DataConnectIndex' },
    children: [
        {
            path: 'list',
            name: 'DataConnectIndex',
            meta: {
                title: 'ROUTER_DATA_CONNECT_PAGE_CONNECT',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "DataConnectIndex" */ '@/pages/DataConnect/List.vue'),
        },
        {
            path: 'detail/:id',
            name: 'DataConnectDetail',
            meta: {
                title: 'ROUTER_DATA_CONNECT_PAGE_DETAIL',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "DataConnectDetail" */ '@/pages/DataConnect/Detail.vue'),
        },
    ],
};

export default dataConnect;
