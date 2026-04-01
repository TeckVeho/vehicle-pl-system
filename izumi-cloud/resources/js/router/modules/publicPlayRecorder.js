const publicPlayRecorder = {
    path: '/public-play-recorder/:id/:index',
    name: 'PublicPlayRecorder',
    meta: {
        title: 'ROUTER_DRIVER_RECORDER',
        icon: 'fas fa-camcorder',
    },
    hidden: true,
    component: () => import(/* webpackChunkName: "PublicPlayRecorderList" */ '@/pages/PublicPlayRecorder/index.vue'),
};

export default publicPlayRecorder;
