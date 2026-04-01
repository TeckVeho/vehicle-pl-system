import Layout from '@/layout';
import CONST_ROLE from '@/const/role';

const dataList = {
    path: '/data-list',
    name: 'DataList',
    meta: {
        title: 'ROUTER_DATA_LIST',
        icon: 'fas fa-folder',
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
    redirect: { name: 'DataListIndex' },
    children: [
        {
            path: 'list',
            name: 'DataListIndex',
            meta: {
                title: 'ROUTER_DATA_LIST_PAGE_LIST',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "DataListIndex" */ '@/pages/DataList/List.vue'),
        },
        {
            path: 'detail/:id',
            name: 'DataListDetail',
            meta: {
                title: 'ROUTER_DATA_LIST_PAGE_DETAIL',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "DataListDetail" */ '@/pages/DataList/Detail.vue'),
        },
    ],
};

export default dataList;
