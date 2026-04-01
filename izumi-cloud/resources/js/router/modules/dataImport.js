import Layout from '@/layout';
import CONST_ROLE from '@/const/role';

const dataImport = {
    path: '/data-import',
    name: 'DataImport',
    meta: {
        title: 'ROUTER_DATA_IMPORT',
        icon: 'fas fa-file-upload',
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
    redirect: { name: 'DataImportIndex' },
    children: [
        {
            path: 'index',
            name: 'DataImportIndex',
            meta: {
                title: 'ROUTER_DATA_CONNECT_PAGE_CONNECT',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "DataImportIndex" */ '@/pages/DataImport/index.vue'),
        },
    ],
};

export default dataImport;
