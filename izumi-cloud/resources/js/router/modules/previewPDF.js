const previewPDF = {
    path: '/preview-pdf',
    name: 'PreviewPDF',
    meta: {
        roles: [],
    },
    hidden: true,
    component: () => import(/* webpackChunkName: "PreviewPDF" */ '@/pages/PreviewPDF/index.vue'),
};

export default previewPDF;
