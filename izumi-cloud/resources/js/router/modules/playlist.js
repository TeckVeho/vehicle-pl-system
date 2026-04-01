
const playlist = {
    path: '/playlist/:id',
    name: 'Playlist',
    hidden: true,
    component: () => import(/* webpackChunkName: "Playlist" */ '@/pages/PlayRecorder/playlist.vue'),
};

export default playlist;
