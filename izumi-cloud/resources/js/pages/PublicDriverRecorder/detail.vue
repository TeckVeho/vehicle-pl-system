<template>
	<b-overlay
		:show="overlay.show"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
		:blur="overlay.blur"
		:rounded="overlay.sm"
	>
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p class="text-loading">{{ $t('PLEASE_WAIT') }}</p>
			</div>
		</template>

		<div class="driver-recorder-detail">
			<div class="driver-recorder-detail__title-header">
				<vHeaderPage>
					{{ $t('PAGE_TITLE.DRIVER_RECONRDER_LIST') }}
				</vHeaderPage>
			</div>

			<div class="driver-recorder-detail__form">
				<div class="item-form">
					<label for="record-date">{{ $t('DRIVER_RECORDER.LABEL_RECORD_DATE') }}</label>
					<b-form-input
						id="form-record-date"
						v-model="isForm.record_date"
						disabled
					/>
				</div>

				<div class="item-form">
					<label for="form-department">{{ $t('DRIVER_RECORDER.LABEL_DEPARTMENT') }}</label>
					<b-form-input
						id="form-record-date"
						v-model="isForm.department"
						disabled
					/>
				</div>

				<!-- 2707 -->
				<div class="item-form">
					<label for="type-one">事故GP</label>
					<b-form-radio-group
						id="type-one"
						v-model="isForm.type_one"
						:options="type_one_options"
						disabled
					/>
				</div>

				<div class="item-form">
					<label for="type-two">有責無責</label>
					<b-form-radio-group
						id="type-two"
						v-model="isForm.type_two"
						:options="type_two_options"
						disabled
					/>
				</div>

				<div class="item-form">
					<label for="shipper">荷主</label>
					<b-form-radio-group
						id="shipper"
						v-model="isForm.shipper"
						:options="shipper_options"
						disabled
					/>
				</div>

				<div class="item-form">
					<label for="accident-classification">事故区分</label>
					<b-form-radio-group
						id="accident-classification"
						v-model="isForm.accident_classification"
						:options="accident_classification_options"
						disabled
					/>
				</div>

				<div class="item-form">
					<label for="place-of-occurrence">発生場所</label>
					<b-form-radio-group
						id="place-of-occurrence"
						v-model="isForm.place_of_occurrence"
						:options="place_of_occurrence_options"
						disabled
					/>
				</div>

				<div class="item-form">
					<label for="form-record-type">{{ $t('DRIVER_RECORDER.LABEL_RECORD_TYPE') }}</label>
					<b-form-radio-group
						id="form-record-type"
						v-model="isForm.record_type"
						:options="listRecordType"
						disabled
					/>
				</div>

				<div class="item-form">
					<label for="form-title">{{ $t('DRIVER_RECORDER.LABEL_TITLE') }}</label>
					<b-form-input
						id="form-title"
						v-model="isForm.title"
						disabled
					/>
				</div>

				<div class="item-form">
					<label for="">{{ $t('DRIVER_RECORDER.LABEL_UPLOAD_FILE') }}</label>
					<UploadFile
						:is-edit="false"
						:sample-upload-data="isForm.listUploadFile"
					/>
				</div>

				<div class="item-form">
					<label for="form-remark">{{ $t('DRIVER_RECORDER.LABEL_REMARK') }}</label>
					<b-form-textarea id="form-remark" v-model="isForm.remark" rows="6" max-rows="6" disabled />
				</div>

				<div class="item-form">
					<label for="">添付画像</label>

					<div class="card-holder">
						<div class="upload-img-card">
							<template v-if="attached_image_1_path && !attached_image_1_is_pdf">
								<img :src="attached_image_1_path" alt="" class="image-card" @click="handleShowImage(attached_image_1_path)">
							</template>

							<template v-else-if="!attached_image_1_path && attached_image_1_is_pdf">
								<div class="pdf-holder" @click="handleShowImage(attached_pdf_1_path, true)">
									<vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_1_path" />
								</div>
							</template>
						</div>

						<div class="upload-img-card">
							<template v-if="attached_image_2_path && !attached_image_2_is_pdf">
								<img :src="attached_image_2_path" alt="" class="image-card" @click="handleShowImage(attached_image_2_path)">
							</template>

							<template v-else-if="!attached_image_2_path && attached_image_2_is_pdf">
								<div class="pdf-holder" @click="handleShowImage(attached_pdf_2_path, true)">
									<vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_2_path" />
								</div>
							</template>
						</div>

						<div class="upload-img-card">
							<template v-if="attached_image_3_path && !attached_image_3_is_pdf">
								<img :src="attached_image_3_path" alt="" class="image-card" @click="handleShowImage(attached_image_3_path)">
							</template>

							<template v-else-if="!attached_image_3_path && attached_image_3_is_pdf">
								<div class="pdf-holder" @click="handleShowImage(attached_pdf_3_path, true)">
									<vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_3_path" />
								</div>
							</template>
						</div>

						<div class="upload-img-card">
							<template v-if="attached_image_4_path && !attached_image_4_is_pdf">
								<img :src="attached_image_4_path" alt="" class="image-card" @click="handleShowImage(attached_image_4_path)">
							</template>

							<template v-else-if="!attached_image_4_path && attached_image_4_is_pdf">
								<div class="pdf-holder" @click="handleShowImage(attached_pdf_4_path, true)">
									<vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_4_path" />
								</div>
							</template>
						</div>

						<div class="upload-img-card">
							<template v-if="attached_image_5_path && !attached_image_5_is_pdf">
								<img :src="attached_image_5_path" alt="" class="image-card" @click="handleShowImage(attached_image_5_path)">
							</template>

							<template v-else-if="!attached_image_5_path && attached_image_5_is_pdf">
								<div class="pdf-holder" @click="handleShowImage(attached_pdf_5_path, true)">
									<vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_5_path" />
								</div>
							</template>
						</div>
					</div>

					<div class="card-holder mt-3">
						<div class="upload-img-card">
							<template v-if="attached_image_6_path && !attached_image_6_is_pdf">
								<img :src="attached_image_6_path" alt="" class="image-card" @click="handleShowImage(attached_image_6_path)">
							</template>

							<template v-else-if="!attached_image_6_path && attached_image_6_is_pdf">
								<div class="pdf-holder" @click="handleShowImage(attached_pdf_6_path, true)">
									<vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_6_path" />
								</div>
							</template>
						</div>

						<div class="upload-img-card">
							<template v-if="attached_image_7_path && !attached_image_7_is_pdf">
								<img :src="attached_image_7_path" alt="" class="image-card" @click="handleShowImage(attached_image_7_path)">
							</template>

							<template v-else-if="!attached_image_7_path && attached_image_7_is_pdf">
								<div class="pdf-holder" @click="handleShowImage(attached_pdf_7_path, true)">
									<vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_7_path" />
								</div>
							</template>
						</div>

						<div class="upload-img-card">
							<template v-if="attached_image_8_path && !attached_image_8_is_pdf">
								<img :src="attached_image_8_path" alt="" class="image-card" @click="handleShowImage(attached_image_8_path)">
							</template>

							<template v-else-if="!attached_image_8_path && attached_image_8_is_pdf">
								<div class="pdf-holder" @click="handleShowImage(attached_pdf_8_path, true)">
									<vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_8_path" />
								</div>
							</template>
						</div>

						<div class="upload-img-card">
							<template v-if="attached_image_9_path && !attached_image_9_is_pdf">
								<img :src="attached_image_9_path" alt="" class="image-card" @click="handleShowImage(attached_image_9_path)">
							</template>

							<template v-else-if="!attached_image_9_path && attached_image_9_is_pdf">
								<div class="pdf-holder" @click="handleShowImage(attached_pdf_9_path, true)">
									<vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_9_path" />
								</div>
							</template>
						</div>

						<div class="upload-img-card">
							<template v-if="attached_image_10_path && !attached_image_10_is_pdf">
								<img :src="attached_image_10_path" alt="" class="image-card" @click="handleShowImage(attached_image_10_path)">
							</template>

							<template v-else-if="!attached_image_10_path && attached_image_10_is_pdf">
								<div class="pdf-holder" @click="handleShowImage(attached_pdf_10_path, true)">
									<vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_10_path" />
								</div>
							</template>
						</div>
					</div>
				</div>

				<div class="item-form mt-3">
					<label for="">添付シート</label>

					<template v-if="attached_file_path">
						<div class="file-preview-card">
							<span>
								<span class="text-preview" @click="handlePreviewFile()">{{ `${attached_file_name} (${attached_file_size} MB)` }}</span>
							</span>
						</div>
					</template>
				</div>

				<b-row>
					<b-col class="text-left">
						<vButton
							:text-button="$t('BUTTON.BACK')"
							:class-name="'btn-radius-default v-button-default btn-registration'"
							@click.native="onClickBack()"
						/>
					</b-col>
				</b-row>

				<b-modal id="modal-show-department-image" v-model="isShowDepartmentImage" :size="modal_is_pdf ? 'xl' : 'lg'" centered hide-footer hide-header>
					<template #default>
						<div class="d-flex w-100" :style="modal_is_pdf ? 'height: 90vh;' : ''">
							<embed v-if="modal_is_pdf" type="video/webm" :src="modal_embed_source" width="100%" height="100%">
							<img v-else :src="modal_image_path" style="width: 100%; height: 100%;" alt="department_image">
						</div>
					</template>
				</b-modal>
			</div>
		</div>
	</b-overlay>
</template>

<script>
import axios from 'axios';
import vButton from '@/components/atoms/vButton';
import vHeaderPage from '@/components/atoms/vHeaderPage';
import VuePdfEmbed from 'vue-pdf-embed/dist/vue2-pdf-embed.js';
import UploadFile from '@/pages/DriverRecorder/components/UploadFile.vue';

import { getListDepartment } from '@/api/modules/driverRecorder';

const urlAPI = {
    apiGetDetailFile: '/api/driver-recorder-viewer',
    apiGetListDepartment: '/department/list-all',
};

export default {
    name: 'PublicDetailDriverRecorder',
    components: {
        vButton,
        UploadFile,
        VuePdfEmbed,
        vHeaderPage,
    },
    data() {
        return {
            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

            items: [],

            listDeparment: [],

            listRecordType: [
                { value: 0, text: this.$t('DRIVER_RECORDER_INDEX.ACCIDENT') },
                { value: 1, text: this.$t('DRIVER_RECORDER_INDEX.OTHER') },
            ],

            isForm: {
                record_date: '',
                department: '',
                record_type: '',
                title: '',
                listUploadFile: [],
                remark: '',
                type_one: null,
                type_two: null,
                shipper: null,
                accident_classification: null,
                place_of_occurrence: null,
            },

            type_one_options: [
                { value: 1, text: '事故', disabled: false },
                { value: 2, text: 'GP', disabled: false },
                { value: 3, text: 'その他', disabled: false },
            ],

            type_two_options: [
                { value: 1, text: '有責', disabled: false },
                { value: 2, text: '無責', disabled: false },
                { value: 3, text: 'その他', disabled: false },
            ],

            shipper_options: [
                { value: 1, text: '山崎製パン', disabled: false },
                { value: 2, text: 'ヤマ物', disabled: false },
                { value: 3, text: 'サンロジ', disabled: false },
                { value: 4, text: '富士エコー', disabled: false },
                { value: 5, text: 'パスコ', disabled: false },
                { value: 6, text: 'ロジネット', disabled: false },
                { value: 7, text: 'FR', disabled: false },
                { value: 8, text: 'その他', disabled: false },
            ],

            accident_classification_options: [
                { value: 1, text: '接触(物)', disabled: false },
                { value: 2, text: '接触(車)', disabled: false },
                { value: 3, text: '接触(人)', disabled: false },
                { value: 4, text: '追突', disabled: false },
                { value: 5, text: 'バック', disabled: false },
                { value: 6, text: '自損横転', disabled: false },
                { value: 7, text: 'オーバーハング', disabled: false },
                { value: 8, text: '巻込み', disabled: false },
                { value: 9, text: '衝突', disabled: false },
                { value: 10, text: '不明・その他', disabled: false },
            ],

            place_of_occurrence_options: [
                { value: 1, text: '店舗敷地', disabled: false },
                { value: 2, text: '構内', disabled: false },
                { value: 3, text: '一般道路', disabled: false },
                { value: 4, text: '交差点', disabled: false },
                { value: 5, text: '高速道路', disabled: false },
                { value: 6, text: '納品口', disabled: false },
                { value: 7, text: '駐車場', disabled: false },
                { value: 8, text: '不明・その他', disabled: false },
            ],

            default_image_path: 'http://thaibinhtv.vn/thumb/640x400/assets/images/imgstd.jpg',

            attached_image_1_path: '',
            attached_image_1_is_pdf: false,
            attached_pdf_1_path: '',

            attached_image_2_path: '',
            attached_image_2_is_pdf: false,
            attached_pdf_2_path: '',

            attached_image_3_path: '',
            attached_image_3_is_pdf: false,
            attached_pdf_3_path: '',

            attached_image_4_path: '',
            attached_image_4_is_pdf: false,
            attached_pdf_4_path: '',

            attached_image_5_path: '',
            attached_image_5_is_pdf: false,
            attached_pdf_5_path: '',

            attached_image_6_path: '',
            attached_image_6_is_pdf: false,
            attached_pdf_6_path: '',

            attached_image_7_path: '',
            attached_image_7_is_pdf: false,
            attached_pdf_7_path: '',

            attached_image_8_path: '',
            attached_image_8_is_pdf: false,
            attached_pdf_8_path: '',

            attached_image_9_path: '',
            attached_image_9_is_pdf: false,
            attached_pdf_9_path: '',

            attached_image_10_path: '',
            attached_image_10_is_pdf: false,
            attached_pdf_10_path: '',

            attached_file_path: '',

            isShowDepartmentImage: false,

            modal_image_path: '',
            modal_is_pdf: false,
            modal_embed_source: '',
        };
    },
    computed: {
        lang() {
            return this.$store.getters.language;
        },
    },
    created() {
        this.initData();

        this.$bus.on('ON_CLICK_PLAY', this.handleNavigateToPlayScreen);
    },
    destroyed() {
        this.$bus.off('ON_CLICK_PLAY', this.handleNavigateToPlayScreen);
    },
    methods: {
        handleNavigateToPlayScreen(index) {
            const route = this.$router.resolve({
                name: 'PlayRecorder',
                params: {
                    id: this.$route.params.id,
                    idx: index,
                },
            });

            window.open(route.href);
        },
        async initData() {
            try {
                this.overlay.show = true;

                await this.handleGetListDepartment();

                await this.handleGetDetailFile();

                this.overlay.show = false;
            } catch (error) {
                console.log(error);
            }
        },
        async handleGetDetailFile() {
            try {
                const URL = `${urlAPI.apiGetDetailFile}/${this.$route.params.id}`;

                const response = await axios.get(URL);

                if (response.data.code === 200) {
                    this.items = response.data.data;

                    const RECORDER_IMAGES = response.data.data.driver_recorder_images;

                    const EXCEL_FILE_INFO = response.data.data.excel;

                    this.isForm.record_date = this.items.record_date;
                    this.isForm.department = this.convertDepartment(this.items.department_id);
                    this.isForm.record_type = this.items.type;
                    this.isForm.title = this.items.title;
                    this.isForm.listUploadFile = this.items.list_recorder;
                    this.isForm.remark = this.items.remark;
                    this.isForm.type_one = this.items.type_one || null;
                    this.isForm.type_two = this.items.type_two || null;
                    this.isForm.shipper = this.items.shipper || null;
                    this.isForm.accident_classification = this.items.accident_classification || null;
                    this.isForm.place_of_occurrence = this.items.place_of_occurrence || null;

                    if (RECORDER_IMAGES.length !== 0) {
                        if (RECORDER_IMAGES.length >= 1) {
                            this.attached_image_1_id = RECORDER_IMAGES[0]['id'];

                            if (RECORDER_IMAGES[0]['file_extension'] === 'pdf') {
                                this.attached_image_1_is_pdf = true;
                                this.attached_pdf_1_path = RECORDER_IMAGES[0]['file_url'];
                            } else {
                                this.attached_image_1_path = RECORDER_IMAGES[0]['file_url'];
                            }
                        }

                        if (RECORDER_IMAGES.length >= 2) {
                            this.attached_image_2_id = RECORDER_IMAGES[1]['id'];

                            if (RECORDER_IMAGES[1]['file_extension'] === 'pdf') {
                                this.attached_image_2_is_pdf = true;
                                this.attached_pdf_2_path = RECORDER_IMAGES[1]['file_url'];
                            } else {
                                this.attached_image_2_path = RECORDER_IMAGES[1]['file_url'];
                            }
                        }

                        if (RECORDER_IMAGES.length >= 3) {
                            this.attached_image_3_id = RECORDER_IMAGES[2]['id'];

                            if (RECORDER_IMAGES[2]['file_extension'] === 'pdf') {
                                this.attached_image_3_is_pdf = true;
                                this.attached_pdf_3_path = RECORDER_IMAGES[2]['file_url'];
                            } else {
                                this.attached_image_3_path = RECORDER_IMAGES[2]['file_url'];
                            }
                        }

                        if (RECORDER_IMAGES.length >= 4) {
                            this.attached_image_4_id = RECORDER_IMAGES[3]['id'];

                            if (RECORDER_IMAGES[3]['file_extension'] === 'pdf') {
                                this.attached_image_4_is_pdf = true;
                                this.attached_pdf_4_path = RECORDER_IMAGES[3]['file_url'];
                            } else {
                                this.attached_image_4_path = RECORDER_IMAGES[3]['file_url'];
                            }
                        }

                        if (RECORDER_IMAGES.length >= 5) {
                            this.attached_image_5_id = RECORDER_IMAGES[4]['id'];

                            if (RECORDER_IMAGES[4]['file_extension'] === 'pdf') {
                                this.attached_image_5_is_pdf = true;
                                this.attached_pdf_5_path = RECORDER_IMAGES[4]['file_url'];
                            } else {
                                this.attached_image_5_path = RECORDER_IMAGES[4]['file_url'];
                            }
                        }

                        if (RECORDER_IMAGES.length >= 6) {
                            this.attached_image_6_id = RECORDER_IMAGES[5]['id'];

                            if (RECORDER_IMAGES[5]['file_extension'] === 'pdf') {
                                this.attached_image_6_is_pdf = true;
                                this.attached_pdf_6_path = RECORDER_IMAGES[5]['file_url'];
                            } else {
                                this.attached_image_6_path = RECORDER_IMAGES[5]['file_url'];
                            }
                        }

                        if (RECORDER_IMAGES.length >= 7) {
                            this.attached_image_7_id = RECORDER_IMAGES[6]['id'];

                            if (RECORDER_IMAGES[6]['file_extension'] === 'pdf') {
                                this.attached_image_7_is_pdf = true;
                                this.attached_pdf_7_path = RECORDER_IMAGES[6]['file_url'];
                            } else {
                                this.attached_image_7_path = RECORDER_IMAGES[6]['file_url'];
                            }
                        }

                        if (RECORDER_IMAGES.length >= 8) {
                            this.attached_image_8_id = RECORDER_IMAGES[7]['id'];

                            if (RECORDER_IMAGES[7]['file_extension'] === 'pdf') {
                                this.attached_image_8_is_pdf = true;
                                this.attached_pdf_8_path = RECORDER_IMAGES[7]['file_url'];
                            } else {
                                this.attached_image_8_path = RECORDER_IMAGES[7]['file_url'];
                            }
                        }

                        if (RECORDER_IMAGES.length >= 9) {
                            this.attached_image_9_id = RECORDER_IMAGES[8]['id'];

                            if (RECORDER_IMAGES[8]['file_extension'] === 'pdf') {
                                this.attached_image_9_is_pdf = true;
                                this.attached_pdf_9_path = RECORDER_IMAGES[8]['file_url'];
                            } else {
                                this.attached_image_9_path = RECORDER_IMAGES[8]['file_url'];
                            }
                        }

                        if (RECORDER_IMAGES.length >= 10) {
                            this.attached_image_10_id = RECORDER_IMAGES[9]['id'];

                            if (RECORDER_IMAGES[9]['file_extension'] === 'pdf') {
                                this.attached_image_10_is_pdf = true;
                                this.attached_pdf_10_path = RECORDER_IMAGES[9]['file_url'];
                            } else {
                                this.attached_image_10_path = RECORDER_IMAGES[9]['file_url'];
                            }
                        }
                    }

                    if (EXCEL_FILE_INFO) {
                        this.attached_file_id = EXCEL_FILE_INFO['id'];
                        this.attached_file_name = EXCEL_FILE_INFO['file_name'];
                        this.attached_file_path = EXCEL_FILE_INFO['file_url'];
                        this.attached_file_size = this.handleRenderFileSize(parseInt(EXCEL_FILE_INFO['file_size']));
                    }
                } else {
                    this.items = [];
                }
            } catch (error) {
                this.items = [];
                console.log(error);
            }
        },
        async handleGetListDepartment() {
            try {
                const { code, data } = await getListDepartment(urlAPI.apiGetListDepartment);

                if (code === 200) {
                    this.listDepartment = data;
                } else {
                    this.listDepartment = [];
                }
            } catch (error) {
                this.listDepartment = [];
                console.log(error);
            }
        },
        handleRenderFileSize(sizeInBytes) {
            const sizeInMB = sizeInBytes / 1024 / 1024;
            return sizeInMB.toFixed(2);
        },
        onClickBack() {
            this.$router.push({ name: 'PublicDriverRecorder' });
        },
        convertDepartment(department_id) {
            const department = this.listDepartment.find((item) => item.id === department_id);

            return department ? department.department_name : '';
        },
        previewImage(event, target) {
            const file = event.target.files[0];

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = (e) => {
                    switch (target) {
                    case 1:
                        this.attached_image_1_path = e.target.result;
                        break;
                    case 2:
                        this.attached_image_2_path = e.target.result;
                        break;
                    case 3:
                        this.attached_image_3_path = e.target.result;
                        break;
                    case 4:
                        this.attached_image_4_path = e.target.result;
                        break;
                    case 5:
                        this.attached_image_5_path = e.target.result;
                        break;
                    case 6:
                        this.attached_image_6_path = e.target.result;
                        break;
                    case 7:
                        this.attached_image_7_path = e.target.result;
                        break;
                    case 8:
                        this.attached_image_8_path = e.target.result;
                        break;
                    case 9:
                        this.attached_image_9_path = e.target.result;
                        break;
                    case 10:
                        this.attached_image_10_path = e.target.result;
                        break;
                    default:
                        break;
                    }
                };

                reader.readAsDataURL(file);
            }
        },
        previewFile(event) {
            const file = event.target.files[0];

            if (file !== null) {
                const reader = new FileReader();

                reader.onload = () => {
                    this.attached_file_path = reader.result;
                };

                reader.readAsDataURL(file);
            }
        },
        handleShowImage(path, is_pdf) {
            if (is_pdf) {
                this.modal_is_pdf = true;
                this.modal_embed_source = path;
            } else {
                this.modal_image_path = path;
            }

            this.isShowDepartmentImage = true;
        },
        handlePreviewFile() {
            const URL = this.attached_file_path;
            window.location.href = URL;
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables';

.pdf-holder {
  &:hover {
    opacity: .4;
    cursor: pointer;
  }
}

::v-deep .vue-pdf-embed__page {
  & > canvas {
    width: 190px !important;
    height: 190px !important;
    object-fit: cover;
    object-position: center;
  }
}

.driver-recorder-detail {
  overflow: hidden;
  min-height: calc(100vh - 89px);

  .text-loading {
    margin-top: 10px;
  }

  &__title-header {
    margin-bottom: 10px;
  }

  &__form {
    margin-bottom: 10px;
    padding: 20px 50px;

    .item-form {
      margin-bottom: 10px;

      label {
        font-weight: bold;
      }
    }
  }
}

.image-card {
  width: 190px;
  height: 190px;
  object-fit: cover;
}

.upload-img-card {
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  width: 200px;
  height: 200px;

  & > span {
    color: #000000;
  }

  &:hover {
    cursor: pointer;
    opacity: .4;
  }
}

.card-holder {
  width: 100%;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
}

.default {
  height: 130px !important;
}

.file-preview-card {
  display: flex;
  position: relative;

  .text-preview {
    color: #0056d2;

    &:hover {
      cursor: pointer;
      text-decoration: underline;
    }
  }
}

@media only screen and (max-width: 768px) {
  .card-holder {
    width: 100%;
    display: flex;
    align-items: center;
    flex-direction: column;

    & > div {
      margin-bottom: 20px;
    }
  }

  .upload-img-card {
    width: 100%;
    height: auto;
    padding: 5px;
  }

  .image-card {
    width: 190px;
    height: 190px;
    object-fit: cover;
  }

  .default {
    height: 190px !important;
  }
}
</style>
