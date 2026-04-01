import Layout from '@/layout';

const videoPlayer = {
    path: '/video-player',
    name: 'VideoPlayer',
    meta: {
        title: 'ROUTER_VIDEO_PLAYER',
        icon: 'fab fa-youtube',
    },
    component: Layout,
    redirect: {
        name: 'VideoPlayerIndex',
    },
    children: [
        {
            path: 'index',
            name: 'VideoPlayerIndex',
            meta: {
                title: 'ROUTER_VIDEO_PLAYER',
            },
            hidden: true,
            component: () => import(/* webpackChunkName: "VideoPlayerIndex" */ '@/pages/VideoPlayer/index.vue'),
        },
    ],
};

export default videoPlayer;
