const publicDriverRecorderDetail = {
    path: '/public-driver-recorder-detail/:id',
    name: 'PublicDriverRecorderDetail',
    hidden: true,
    meta: {},
    component: () => import(/* webpackChunkName: "publicDriverRecorderDetail" */ '@/pages/PublicDriverRecorder/detail.vue'),
};

export default publicDriverRecorderDetail;
