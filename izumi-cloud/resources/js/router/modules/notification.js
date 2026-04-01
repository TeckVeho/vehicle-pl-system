const notification = {
    path: '/notification',
    name: 'Notification',
    meta: {
        roles: [],
    },
    hidden: true,
    component: () => import(/* webpackChunkName: "Notification" */ '@/pages/Notification/index.vue'),
};

export default notification;
