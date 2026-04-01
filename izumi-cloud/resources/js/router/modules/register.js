const register = {
    path: '/register',
    name: 'Register',
    meta: {
        roles: [],
    },
    hidden: true,
    component: () => import(/* webpackChunkName: "Register" */ '@/pages/Register/index.vue'),
};

export default register;
