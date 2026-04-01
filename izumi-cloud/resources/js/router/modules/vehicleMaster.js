import Layout from '@/layout';
// import CONST_ROLE from '@/const/role';

const vehicleMaster = {
    path: '/vehicle-master',
    name: 'VehicleMaster',
    meta: {
        title: 'ROUTER_VEHICLE_MASTER',
        icon: 'fas fa-truck-moving  ',
    },
    component: Layout,
    redirect: {
        name: 'VehicleMasterList',
    },
    children: [
        {
            path: 'list',
            name: 'VehicleMasterList',
            meta: {
                title: 'ROUTER_VEHICLE_MASTER',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "VehicleMasterList" */ '@/pages/VehicleMaster/index.vue'),
        },
        {
            path: 'create',
            name: 'VehicleMasterCreate',
            meta: {
                title: 'ROUTER_VEHICLE_MASTER',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "VehicleMasterUpload" */ '@/pages/VehicleMaster/create.vue'),
        },
        {
            path: 'detail/:id',
            name: 'VehicleMasterDetail',
            meta: {
                title: 'ROUTER_VEHICLE_MASTER',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "VehicleMasterDetail" */ '@/pages/VehicleMaster/detail.vue'),
        },
        {
            path: 'edit/:id',
            name: 'VehicleMasterEdit',
            meta: {
                title: 'ROUTER_VEHICLE_MASTER',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "VehicleMasterEdit" */ '@/pages/VehicleMaster/exdit.vue'),
        },
    ],
};

export default vehicleMaster;
