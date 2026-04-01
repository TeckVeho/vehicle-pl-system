const resetPassword = {
    path: '/reset-password',
    name: 'ResetPassword',
    meta: {
        roles: [],
    },
    hidden: true,
    component: () => import(/* webpackChunkName: "ResetPassword" */ '@/pages/ResetPassword/index.vue'),
};

export default resetPassword;
