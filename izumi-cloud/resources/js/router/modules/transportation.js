import Layout from '@/layout';

const transportation = {
    path: '/transportation',
    name: 'Transportation',
    meta: {
        title: 'ROUTER_TRANSPORTATION',
        icon: 'fas fa-bezier-curve',
    },
    component: Layout,
    redirect: {
        name: 'TransportationIndex',
    },
    children: [
        {
            path: 'index',
            name: 'TransportationIndex',
            meta: {
                title: 'ROUTER_TRANSPORTATION',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "Transportation" */ '@/pages/Transportation/index.vue'),
        },
    ],
};

export default transportation;
