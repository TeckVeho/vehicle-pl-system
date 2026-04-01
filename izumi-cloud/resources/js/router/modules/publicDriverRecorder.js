const publicDriverRecorder = {
    path: '/public-driver-recorder',
    name: 'PublicDriverRecorder',
    meta: {
        title: 'ROUTER_DRIVER_RECORDER',
        icon: 'fas fa-camcorder',
    },
    hidden: true,
    component: () => import(/* webpackChunkName: "PublicDriverRecorderList" */ '@/pages/PublicDriverRecorder/index.vue'),
};

export default publicDriverRecorder;
