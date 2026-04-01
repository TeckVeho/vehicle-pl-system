const authMobile = {
    path: '/auth-mobile',
    name: 'AuthMobile',
    meta: {
        roles: [],
    },
    hidden: true,
    component: () => import(/* webpackChunkName: "AuthMobile" */ '@/pages/AuthMobile/index.vue'),
};

export default authMobile;
