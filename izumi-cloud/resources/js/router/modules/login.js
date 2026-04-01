const login = {
    path: '/login',
    name: 'Login',
    meta: {
        roles: [],
    },
    hidden: true,
    component: () => import(/* webpackChunkName: "Login" */ '@/pages/Login/index.vue'),
};

export default login;
