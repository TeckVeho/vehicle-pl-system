const publicPlaylist = {
    path: '/public-playlist/:id',
    name: 'PublicPlaylist',
    hidden: true,
    meta: {},
    component: () => import(/* webpackChunkName: "PublicPlaylist" */ '@/pages/PublicDriverRecorder/playlist.vue'),
};

export default publicPlaylist;
