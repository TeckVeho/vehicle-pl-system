import Layout from '@/layout';

const errorRoutes = {
    path: '/error',
    component: Layout,
    name: 'ErrorPages',
    meta: {
        title: 'ROUTER_ERROR_PAGE',
    },
    redirect: { name: 'Page404' },
    hidden: true,
    children: [
        {
            path: '404',
            component: () => import('@/pages/ErrorPage/index'),
            name: 'Page404',
            meta: { title: 'ROUTER_ERROR_PAGE' },
        },
    ],
};

export default errorRoutes;
