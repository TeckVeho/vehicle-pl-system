import 'vue2-dropzone/dist/vue2Dropzone.min.css';

import vue2Dropzone from 'vue2-dropzone';
import draggable from 'vuedraggable';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';

import { obj2Path } from '@/utils/obj2Path';
import { cleanObj } from '@/utils/handleObj';
import { getListFiles, deleteFiles, changeOrder } from '@/api/modules/pocketBook';

export default {
    name: 'PocketBook',
    components: {
        vHeaderPage,
        vuedropzone: vue2Dropzone,
        draggable,
    },
    data() {
        return {
            overlay: {
                opacity: 1,
                show: false,
                blur: '1rem',
                rounded: 'sm',
                variant: 'light',
            },
            sub_overlay: {
                opacity: 1,
                show: false,
                blur: '1rem',
                rounded: 'sm',
                variant: 'light',
            },
            date: new Date().getFullYear(),
            files: [],
            uploadedFiles: [],
            showModalDeleteFile: false,
            idFileDelete: '',
            showModalPdf: false,
            pdfUrl: '',
            showModalUpload: false,
            fileType: null,
            fileTypeOptions: [
                { value: null, text: this.$t('PLEASE_SELECT'), disabled: true },
                { value: 1, text: '経営理念', disabled: false },
                { value: 2, text: '田中孝一名誉会長講義録', disabled: false },
                { value: 3, text: 'ダイセーグループの“遺伝子”-17のKey Word', disabled: false },
                { value: 4, text: 'IZUMIフィロソフィ', disabled: false },
            ],
            isShowValidtionText: false,
            dragging: false,
            totalFilesToUpload: 0,
            completedFilesCount: 0,
            uploadingFiles: [],
        };
    },
    computed: {
        options_date() {
            const currentYear = new Date().getFullYear();
            const years = [];

            for (let year = 2025; year <= currentYear + 1; year++) {
                years.push({ value: year, text: `${year} 年度` });
            }

            return years;
        },
        dropzoneFileOptions() {
            return {
                method: 'POST',
                chunking: true,
                maxFiles: null,
                chunkSize: 10000000,
                uploadMultiple: false,
                parallelChunkUploads: false,
                acceptedFiles: '.pdf, image/*',
                previewTemplate: this.template(),
                maxFilesize: 1 * 1024 * 1024 * 1024,
                headers: { 'Authorization': this.$store.getters.token },
                url: `${window.origin}/api/pocket-book/upload-file?year=${this.date || new Date().getFullYear()}&tag=${this.fileType}`,
            };
        },
    },
    watch: {
        fileType() {
            this.isShowValidtionText = this.fileType === null;
        },
    },
    created() {
        this.initData();
    },
    methods: {
        template() {
            return `<div></div>`;
        },
        async initData() {
            this.overlay.show = true;
            await this.handleGetListFile();
            this.overlay.show = false;
        },
        afterAdded(file) {
            const reader = new FileReader();

            this.uploadingFiles.push({
                id: null,
                file_name: file.name,
                progressPercentage: 0,
                progressBandwidth: 0,
                file_url: null,
            });

            this.totalFilesToUpload++;

            reader.readAsDataURL(file);
        },
        async afterComplete(response) {
            await this.$nextTick();

            this.completedFilesCount++;

            if (response.xhr.response) {
                try {
                    if (response.xhr.status === 201) {
                        const index = this.uploadingFiles.findIndex(f => f.file_name === response.name);

                        if (index !== -1) {
                            this.uploadingFiles[index].progressPercentage = 100;
                        }

                        console.log(`Upload thành công: ${response.name} (${this.completedFilesCount}/${this.totalFilesToUpload})`);
                    } else {
                        console.error('Upload thất bại:', response.name, response.xhr.status);
                    }
                } catch (error) {
                    console.error('Error in afterComplete:', error);
                }
            }

            if (this.completedFilesCount >= this.totalFilesToUpload) {
                this.sub_overlay.show = false;

                const successCount = this.completedFilesCount;
                const totalCount = this.totalFilesToUpload;

                if (successCount === totalCount) {
                    this.$toast.success({
                        content: `${totalCount}件のファイルが正常にアップロードされました`,
                    });
                } else {
                    this.$toast.warning({
                        content: `${successCount}/${totalCount}件のファイルがアップロードされました`,
                    });
                }

                await this.handleGetListFile(this.date);

                this.showModalUpload = false;
                this.overlay.show = false;

                this.totalFilesToUpload = 0;
                this.completedFilesCount = 0;
                this.uploadingFiles = [];
            }
        },
        async onUploadProgress(file) {
            this.sub_overlay.show = true;

            const _progress = file.upload.progress;
            const _bytesSent = file.upload.bytesSent;

            if (_progress !== 100) {
                const index = this.uploadingFiles.findIndex(f => f.file_name === file.name);

                if (index !== -1) {
                    this.uploadingFiles[index].progressPercentage = _progress;
                    this.uploadingFiles[index].progressBandwidth = _bytesSent;
                }
            }
        },
        transformPercentage(percentage) {
            if (percentage) {
                return percentage.toFixed(2);
            } else {
                return 0;
            }
        },
        transformBandwidth(bandwidth) {
            if (bandwidth) {
                return (bandwidth / 1000000).toFixed(2);
            } else {
                return 0;
            }
        },
        beforeSend(file, xhr, formData) {
            formData.append('year', this.date || new Date().getFullYear());
        },
        handleClosefiles(id) {
            this.idFileDelete = id;
            this.showModalDeleteFile = true;
        },
        async handleDelete() {
            try {
                this.overlay.show = true;

                this.showModalDeleteFile = false;

                const URL = `/pocket-book/${this.idFileDelete}`;

                const response = await deleteFiles(URL);

                if (response.code === 200) {
                    this.$toast.success({ content: 'ファイルが正常に削除されました' });
                    this.handleGetListFile();
                }

                this.overlay.show = true;
            } catch (error) {
                console.error('[354] -> [handleDelete] ==>', error);
                this.overlay.show = false;
            } finally {
                this.overlay.show = false;
            }
        },
        async onError(event) {
            console.error('Upload error:', event);

            this.completedFilesCount++;

            const index = this.uploadingFiles.findIndex(f => f.file_name === event.name);
            if (index !== -1) {
                this.uploadingFiles[index].progressPercentage = -1;
            }

            if (this.completedFilesCount >= this.totalFilesToUpload) {
                this.sub_overlay.show = false;

                const failedCount = this.uploadingFiles.filter(f => f.progressPercentage === -1).length;
                const successCount = this.totalFilesToUpload - failedCount;

                if (failedCount > 0) {
                    this.$toast.warning({
                        content: `${successCount}/${this.totalFilesToUpload}件のファイルがアップロードされました。${failedCount}件失敗しました。`,
                    });
                }

                await this.handleGetListFile(this.date);

                this.showModalUpload = false;
                this.overlay.show = false;

                this.totalFilesToUpload = 0;
                this.completedFilesCount = 0;
                this.uploadingFiles = [];
            }
        },
        async handleGetListFile(date) {
            this.listTags = [];

            let URL = {};
            URL.year = date || this.date;

            URL = cleanObj(URL);
            URL = `/pocket-book?${obj2Path(URL)}`;

            const response = await getListFiles(URL);

            if (response.code === 200) {
                const rawArray = response.data.map(file => ({
                    id: file.id,
                    tag: parseInt(file.tag),
                    file_name: file.file?.file_name,
                    created_at: file.created_at || null,
                    file_url: file.file?.file_url || '#',
                    progressPercentage: file.progressPercentage || 100,
                }));

                const tagNameMap = {
                    1: '経営理念',
                    2: '田中孝一名誉会長講義録',
                    3: 'ダイセーグループの“遺伝子”-17のKey Word',
                    4: 'IZUMIフィロソフィ',
                };

                const groupedFiles = rawArray.reduce((acc, file) => {
                    const tag = acc.find(item => item.tag_name === tagNameMap[file.tag]);

                    if (!tag) {
                        acc.push({
                            tag_name: tagNameMap[file.tag] || 'Unknown Category',
                            data: [file],
                        });
                    } else {
                        tag.data.push(file);
                    }

                    return acc;
                }, []);

                this.files = [...groupedFiles];
            }
        },
        onDateChange(date) {
            this.date = date;
            this.handleGetListFile(date);
        },
        goToDownload(url) {
            this.pdfUrl = url;
            this.showModalPdf = true;
        },
        handleOpenModalUploadFile() {
            this.totalFilesToUpload = 0;
            this.completedFilesCount = 0;
            this.uploadingFiles = [];

            this.showModalUpload = true;
            this.isShowValidtionText = false;
        },
        handleClickNonUpdate() {
            this.isShowValidtionText = true;
        },
        handleModalUploadClose() {
            this.fileType = null;

            this.totalFilesToUpload = 0;
            this.completedFilesCount = 0;
            this.uploadingFiles = [];

            if (this.$refs.myVueDropzoneFile) {
                this.$refs.myVueDropzoneFile.removeAllFiles(true);
            }
        },
        async dragChanged(groupIndex) {
            const group = this.files[groupIndex];
            const orderData = [];

            for (let index = 0; index < group.data.length; index++) {
                orderData.push(group.data[index].id);
            }

            await this.handleChangeOrder(orderData);
        },
        async handleChangeOrder(list_pocket_book) {
            try {
                const URL = '/pocket-book/change-order';
                const DATA = {
                    list_pocket_book: list_pocket_book,
                };

                console.log('Payload to API:', DATA);

                const response = await changeOrder(URL, DATA);

                if (response['code'] === 200) {
                    await this.handleGetListFile(this.date);

                    this.$toast.success({
                        title: '成功',
                        content: '順番が正常に更新されました',
                    });
                } else {
                    this.$toast.warning({
                        content: response['message'],
                    });
                }
            } catch (error) {
                console.error('[ERROR] -> [handleChangeOrder] ==>', error);
            }
        },
    },
};

