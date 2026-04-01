import Layout from '@/layout';

const dev = {
    path: '/dev',
    name: 'Dev',
    meta: {
        title: 'ROUTER_DEV',
        icon: 'fab fa-dev',
        roles: [],
    },
    hidden: true,
    component: Layout,
    redirect: { name: 'DevIndex' },
    children: [
        {
            path: 'index',
            name: 'DevIndex',
            meta: {
                title: 'ROUTER_DEV',
            },
            component: () => import(/* webpackChunkName: "DevIndex" */ '@/pages/Dev/index.vue'),
        },
    ],
};

export default dev;
