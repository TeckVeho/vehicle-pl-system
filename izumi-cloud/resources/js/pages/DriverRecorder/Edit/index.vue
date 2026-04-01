<!-- eslint-disable vue/html-comment-indent -->
<!-- eslint-disable vue/html-indent -->
<template>
    <b-overlay :show="overlay.show" :variant="overlay.variant" :opacity="overlay.opacity" :blur="overlay.blur" :rounded="overlay.sm">
        <template #overlay>
            <div class="text-center">
                <b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
                <p class="text-loading">{{ $t('PLEASE_WAIT') }}</p>
            </div>
        </template>

        <div class="driver-recorder-edit">
            <div class="driver-recorder-edit__title-header">
                <vHeaderPage>
                    {{ $t('PAGE_TITLE.DRIVER_RECONRDER_LIST') }}
                </vHeaderPage>
            </div>

            <div class="driver-recorder-edit__form">
                <div class="item-form">
                    <label for="record-date">{{ $t('DRIVER_RECORDER.LABEL_RECORD_DATE') }}</label>
                    <b-form-datepicker id="form-record-date" v-model="isForm.record_date" :locale="lang" placeholder="入力してください" :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }" />
                </div>

                <div class="item-form">
                    <label for="form-department">{{ $t('DRIVER_RECORDER.LABEL_DEPARTMENT') }}</label>
                    <b-form-select id="form-department" v-model="isForm.department" :options="listDepartment" :value-field="`id`" :text-field="`department_name`">
                        <template #first>
                            <b-form-select-option :value="null" :disabled="true">
                                {{ $t('PLEASE_SELECT') }}
                            </b-form-select-option>
                        </template>
                    </b-form-select>
                </div>

                <!-- 2707 -->
                <div class="item-form">
                    <label for="type-one">事故GP</label>
                    <b-form-radio-group id="type-one" v-model="isForm.type_one" :options="type_one_options" />
                </div>

                <div class="item-form">
                    <label for="type-two">有責無責</label>
                    <b-form-radio-group id="type-two" v-model="isForm.type_two">
                        <b-form-radio value="1" class="custom-radio-option">
                            <span :class="handleRenderColor(isForm.type_two)">有責</span>
                        </b-form-radio>
                        <b-form-radio value="2" class="custom-radio-option">無責</b-form-radio>
                        <b-form-radio value="3" class="custom-radio-option">その他</b-form-radio>
                    </b-form-radio-group>
                </div>

                <div class="item-form">
                    <label for="shipper">荷主</label>
                    <b-form-radio-group id="shipper" v-model="isForm.shipper" :options="shipper_options" />
                </div>

                <div class="item-form">
                    <label for="accident-classification">事故区分</label>
                    <b-form-radio-group id="accident-classification" v-model="isForm.accident_classification" :options="accident_classification_options" />
                </div>

                <div class="item-form">
                    <label for="place-of-occurrence">発生場所</label>
                    <b-form-radio-group id="place-of-occurrence" v-model="isForm.place_of_occurrence" :options="place_of_occurrence_options" />
                </div>

                <div class="item-form">
                    <label for="form-title">{{ $t('DRIVER_RECORDER.LABEL_TITLE') }}</label>
                    <b-form-input id="form-title" v-model="isForm.title" :placeholder="'入力してください'" />
                </div>

                <div class="item-form">
                    <label for="">{{ $t('DRIVER_RECORDER.LABEL_UPLOAD_FILE') }}</label>
                    <EditUploadFile :is-edit="true" :sample-upload-data="sampleUploadData" @add="eventAddTableUploadFile" @delete="eventDeleteTableUploadFile" />
                </div>

                <div class="item-form">
                    <label for="form-remark">{{ $t('DRIVER_RECORDER.LABEL_REMARK') }}</label>
                    <b-form-textarea id="form-remark" v-model="isForm.remark" rows="6" max-rows="6" :placeholder="'入力してください'" />
                </div>

                <div class="item-form">
                    <label for="">添付画像</label>

                    <div class="d-flex-row justify-content-between">
                        <div class="card-holder">
                            <template v-if="attached_image_1_path && !attached_image_1_is_pdf">
                                <img :src="attached_image_1_path" alt="" class="image-card" @click="handleShowImage(attached_image_1_path)">
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(1)" />
                            </template>

                            <template v-else-if="!attached_image_1_path && attached_image_1_is_pdf">
                                <div class="pdf-holder" @click="handleShowImage(attached_pdf_1_path, true)">
                                    <vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_1_path" />
                                </div>
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(1)" />
                            </template>

                            <template v-else>
                                <vuedropzone id="customdropzoneimage1" ref="myVueDropzoneImage1" :use-custom-slot="true" :include-styling="false" :options="dropzoneImageOptions" @vdropzone-file-added="afterAdded($event, 1)" @vdropzone-complete="afterComplete($event, 1)">
                                    <div class="upload-img-card">
                                        <div class="d-row flex-row text-center">
                                            <i class="fa fa-cloud-upload" />
                                            <span>アップロード</span>
                                        </div>
                                    </div>
                                </vuedropzone>
                            </template>
                        </div>

                        <div class="card-holder">
                            <template v-if="attached_image_2_path && !attached_image_2_is_pdf">
                                <img :src="attached_image_2_path" alt="" class="image-card" @click="handleShowImage(attached_image_2_path)">
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(2)" />
                            </template>

                            <template v-else-if="!attached_image_2_path && attached_image_2_is_pdf">
                                <div class="pdf-holder" @click="handleShowImage(attached_pdf_2_path, true)">
                                    <vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_2_path" />
                                </div>
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(2)" />
                            </template>

                            <template v-if="!attached_image_2_path && !attached_pdf_2_path">
                                <vuedropzone id="customdropzoneimage2" ref="myVueDropzoneImage2" :use-custom-slot="true" :include-styling="false" :options="dropzoneImageOptions" @vdropzone-file-added="afterAdded($event, 2)" @vdropzone-complete="afterComplete($event, 2)">
                                    <div class="upload-img-card">
                                        <div class="d-row flex-row text-center">
                                            <i class="fa fa-cloud-upload" />
                                            <span>アップロード</span>
                                        </div>
                                    </div>
                                </vuedropzone>
                            </template>
                        </div>

                        <div class="card-holder">
                            <template v-if="attached_image_3_path && !attached_image_3_is_pdf">
                                <img :src="attached_image_3_path" alt="" class="image-card" @click="handleShowImage(attached_image_3_path)">
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(3)" />
                            </template>

                            <template v-else-if="!attached_image_3_path && attached_image_3_is_pdf">
                                <div class="pdf-holder" @click="handleShowImage(attached_pdf_3_path, true)">
                                    <vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_3_path" />
                                </div>
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(3)" />
                            </template>

                            <template v-else>
                                <vuedropzone id="customdropzoneimage3" ref="myVueDropzoneImage3" :use-custom-slot="true" :include-styling="false" :options="dropzoneImageOptions" @vdropzone-file-added="afterAdded($event, 3)" @vdropzone-complete="afterComplete($event, 3)">
                                    <div class="upload-img-card">
                                        <div class="d-row flex-row text-center">
                                            <i class="fa fa-cloud-upload" />
                                            <span>アップロード</span>
                                        </div>
                                    </div>
                                </vuedropzone>
                            </template>
                        </div>

                        <div class="card-holder">
                            <template v-if="attached_image_4_path && !attached_image_4_is_pdf">
                                <img :src="attached_image_4_path" alt="" class="image-card" @click="handleShowImage(attached_image_4_path)">
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(4)" />
                            </template>

                            <template v-else-if="!attached_image_4_path && attached_image_4_is_pdf">
                                <div class="pdf-holder" @click="handleShowImage(attached_pdf_4_path, true)">
                                    <vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_4_path" />
                                </div>
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(4)" />
                            </template>

                            <template v-else>
                                <vuedropzone id="customdropzoneimage4" ref="myVueDropzoneImage4" :use-custom-slot="true" :include-styling="false" :options="dropzoneImageOptions" @vdropzone-file-added="afterAdded($event, 4)" @vdropzone-complete="afterComplete($event, 4)">
                                    <div class="upload-img-card">
                                        <div class="d-row flex-row text-center">
                                            <i class="fa fa-cloud-upload" />
                                            <span>アップロード</span>
                                        </div>
                                    </div>
                                </vuedropzone>
                            </template>
                        </div>

                        <div class="card-holder">
                            <template v-if="attached_image_5_path && !attached_image_5_is_pdf">
                                <img :src="attached_image_5_path" alt="" class="image-card" @click="handleShowImage(attached_image_5_path)">
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(5)" />
                            </template>

                            <template v-else-if="!attached_image_5_path && attached_image_5_is_pdf">
                                <div class="pdf-holder" @click="handleShowImage(attached_pdf_5_path, true)">
                                    <vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_5_path" />
                                </div>
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(5)" />
                            </template>

                            <template v-else>
                                <vuedropzone id="customdropzoneimage5" ref="myVueDropzoneImage5" :use-custom-slot="true" :include-styling="false" :options="dropzoneImageOptions" @vdropzone-file-added="afterAdded($event, 5)" @vdropzone-complete="afterComplete($event, 5)">
                                    <div class="upload-img-card">
                                        <div class="d-row flex-row text-center">
                                            <i class="fa fa-cloud-upload" />
                                            <span>アップロード</span>
                                        </div>
                                    </div>
                                </vuedropzone>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="d-flex-row justify-content-between mt-3">
                    <div class="card-holder">
                        <template v-if="attached_image_6_path && !attached_image_6_is_pdf">
                            <img :src="attached_image_6_path" alt="" class="image-card" @click="handleShowImage(attached_image_6_path)">
                            <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(6)" />
                        </template>

                        <template v-else-if="!attached_image_6_path && attached_image_6_is_pdf">
                            <div class="pdf-holder" @click="handleShowImage(attached_pdf_6_path, true)">
                                <vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_6_path" />
                            </div>
                            <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(6)" />
                        </template>

                        <template v-else>
                            <vuedropzone id="customdropzoneimage6" ref="myVueDropzoneImage6" :use-custom-slot="true" :include-styling="false" :options="dropzoneImageOptions" @vdropzone-file-added="afterAdded($event, 6)" @vdropzone-complete="afterComplete($event, 6)">
                                <div class="upload-img-card">
                                    <div class="d-row flex-row text-center">
                                        <i class="fa fa-cloud-upload" />
                                        <span>アップロード</span>
                                    </div>
                                </div>
                            </vuedropzone>
                        </template>
                    </div>

                    <div class="card-holder">
                        <template v-if="attached_image_7_path && !attached_image_7_is_pdf">
                            <img :src="attached_image_7_path" alt="" class="image-card" @click="handleShowImage(attached_image_7_path)">
                            <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(7)" />
                        </template>

                        <template v-else-if="!attached_image_7_path && attached_image_7_is_pdf">
                            <div class="pdf-holder" @click="handleShowImage(attached_pdf_7_path, true)">
                                <vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_7_path" />
                            </div>
                            <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(7)" />
                        </template>

                        <template v-else>
                            <vuedropzone id="customdropzoneimage7" ref="myVueDropzoneImage7" :use-custom-slot="true" :include-styling="false" :options="dropzoneImageOptions" @vdropzone-file-added="afterAdded($event, 7)" @vdropzone-complete="afterComplete($event, 7)">
                                <div class="upload-img-card">
                                    <div class="d-row flex-row text-center">
                                        <i class="fa fa-cloud-upload" />
                                        <span>アップロード</span>
                                    </div>
                                </div>
                            </vuedropzone>
                        </template>
                    </div>

                    <div class="card-holder">
                        <template v-if="attached_image_8_path && !attached_image_8_is_pdf">
                            <img :src="attached_image_8_path" alt="" class="image-card" @click="handleShowImage(attached_image_8_path)">
                            <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(8)" />
                        </template>

                        <template v-else-if="!attached_image_8_path && attached_image_8_is_pdf">
                            <div class="pdf-holder" @click="handleShowImage(attached_pdf_8_path, true)">
                                <vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_8_path" />
                            </div>
                            <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(8)" />
                        </template>

                        <template v-else>
                            <vuedropzone id="customdropzoneimage8" ref="myVueDropzoneImage8" :use-custom-slot="true" :include-styling="false" :options="dropzoneImageOptions" @vdropzone-file-added="afterAdded($event, 8)" @vdropzone-complete="afterComplete($event, 8)">
                                <div class="upload-img-card">
                                    <div class="d-row flex-row text-center">
                                        <i class="fa fa-cloud-upload" />
                                        <span>アップロード</span>
                                    </div>
                                </div>
                            </vuedropzone>
                        </template>
                    </div>

                    <div class="card-holder">
                        <template v-if="attached_image_9_path && !attached_image_9_is_pdf">
                            <img :src="attached_image_9_path" alt="" class="image-card" @click="handleShowImage(attached_image_9_path)">
                            <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(9)" />
                        </template>

                        <template v-else-if="!attached_image_9_path && attached_image_9_is_pdf">
                            <div class="pdf-holder" @click="handleShowImage(attached_pdf_9_path, true)">
                                <vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_9_path" />
                            </div>
                            <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(9)" />
                        </template>

                        <template v-else>
                            <vuedropzone id="customdropzoneimage9" ref="myVueDropzoneImage9" :use-custom-slot="true" :include-styling="false" :options="dropzoneImageOptions" @vdropzone-file-added="afterAdded($event, 9)" @vdropzone-complete="afterComplete($event, 9)">
                                <div class="upload-img-card">
                                    <div class="d-row flex-row text-center">
                                        <i class="fa fa-cloud-upload" />
                                        <span>アップロード</span>
                                    </div>
                                </div>
                            </vuedropzone>
                        </template>
                    </div>

                    <div class="card-holder">
                        <template v-if="attached_image_10_path && !attached_image_10_is_pdf">
                            <img :src="attached_image_10_path" alt="" class="image-card" @click="handleShowImage(attached_image_10_path)">
                            <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(10)" />
                        </template>

                        <template v-else-if="!attached_image_10_path && attached_image_10_is_pdf">
                            <div class="pdf-holder" @click="handleShowImage(attached_pdf_10_path, true)">
                                <vue-pdf-embed :width="'190'" :height="'190'" :page="1" :source="attached_pdf_10_path" />
                            </div>
                            <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(10)" />
                        </template>

                        <template v-else>
                            <vuedropzone id="customdropzoneimage10" ref="myVueDropzoneImage10" :use-custom-slot="true" :include-styling="false" :options="dropzoneImageOptions" @vdropzone-file-added="afterAdded($event, 10)" @vdropzone-complete="afterComplete($event, 10)">
                                <div class="upload-img-card">
                                    <div class="d-row flex-row text-center">
                                        <i class="fa fa-cloud-upload" />
                                        <span>アップロード</span>
                                    </div>
                                </div>
                            </vuedropzone>
                        </template>
                    </div>
                </div>

                <div class="item-form mt-3">
                    <label for="">添付シート</label>

                    <template v-if="attached_file_path">
                        <div class="file-preview-card">
                            <span>
                                <span class="text-preview" @click="handlePreviewFile()">{{ `${attached_file_name}
                                    (${attached_file_size} MB)` }}</span>
                                <i class="fas fa-times-circle icon-preview" @click="handleRemoveFile(11)" />
                            </span>
                        </div>
                    </template>

                    <template v-else>
                        <vuedropzone id="customdropzonefile" ref="myVueDropzoneFile" :use-custom-slot="true" :include-styling="false" :options="dropzoneFileOptions" @vdropzone-file-added="afterAdded($event, 11)" @vdropzone-complete="afterComplete($event, 11)">
                            <div class="dropzone-custom-content">
                                <div class="d-row flex-row text-center">
                                    <i class="fa fa-cloud-upload" />
                                    <span>アップロード</span>
                                </div>
                            </div>
                        </vueDropzone>
                    </template>
                </div>

                <div class="d-flex flex-row w-100 mt-3">
                    <div class="d-flex w-50 justify-content-start">
                        <vButton :text-button="$t('BUTTON.BACK')" :class-name="'btn-radius-default v-button-default btn-registration'" :disabled="hasProcessRunning" @click.native="onClickBack()" />
                    </div>

                    <div class="d-flex flex-row w-50 justify-content-end align-items-center">
                        <b-form-checkbox v-model="isForm.is_create_with_notice" size="lg" class="mr-3" value="accepted" unchecked-value="not_accepted" :disabled="isForm.flag_send_noti === 1">
                            <span style="color: red;">お知らせを自動作成する</span>
                        </b-form-checkbox>

                        <vButton :text-button="$t('BUTTON.SAVE')" :class-name="'btn-radius-default v-button-default btn-registration'" :disabled="hasProcessRunning" @click.native="onClickSave()" />
                    </div>
                </div>

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
import vue2Dropzone from 'vue2-dropzone';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';
import VuePdfEmbed from 'vue-pdf-embed/dist/vue2-pdf-embed.js';

import axios from 'axios';
import CONST_ROLE from '@/const/role';
import vButton from '@/components/atoms/vButton';
import vHeaderPage from '@/components/atoms/vHeaderPage';
import EditUploadFile from '../components/EditUploadFile.vue';

import { MakeToast } from '@/utils/MakeToast';
import { getListUserByDepartment } from '@/api/modules/user';
import { getDetailFile, getListDepartment, putUploadData } from '@/api/modules/driverRecorder';

const urlAPI = {
    apiSaveFile: '/driver-recorder',
    apiGetDetailFile: '/driver-recorder',
    apiGetListDepartment: '/department/list-all',
    apiGetListUserByDepartment: '/user/department',
};

export default {
    name: 'EditDriverRecorder',
    components: {
        vButton,
        VuePdfEmbed,
        vHeaderPage,
        EditUploadFile,
        vuedropzone: vue2Dropzone,
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

            hasProcessRunning: false,

            items: [],

            listDepartment: [],
            listCrewMemberByDepartment: [],

            listRecordType: [
                { value: 0, text: this.$t('DRIVER_RECORDER_INDEX.ACCIDENT') },
                { value: 1, text: this.$t('DRIVER_RECORDER_INDEX.OTHER') },
            ],

            sampleUploadData: [],

            isForm: {
                record_date: '',
                department: null,
                crew_member: null,
                title: '',
                listUploadFile: [],
                remark: '',
                type_one: null,
                type_two: null,
                shipper: null,
                accident_classification: null,
                place_of_occurrence: null,
                is_create_with_notice: 'not_accepted',
                flag_send_noti: 0,
                driver_recorder_id: null,
            },

            role: this.$store.getters.profile.roles[0],

            hasAccess: [
                CONST_ROLE.CLERKS,
                CONST_ROLE.TL,
                CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                CONST_ROLE.AM_SM,
                CONST_ROLE.QUALITY_CONTROL,
                CONST_ROLE.SITE_MANAGER,
                CONST_ROLE.HQ_MANAGER,
                CONST_ROLE.DEPARTMENT_MANAGER,
                CONST_ROLE.EXECUTIVE_OFFICER,
                CONST_ROLE.DIRECTOR,
                CONST_ROLE.DX_USER,
                CONST_ROLE.DX_MANAGER,
            ],

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

            dropzoneFileOptions: {
                method: 'POST',
                headers: { 'Authorization': this.$store.getters.token },
                url: `${window.origin}/api/driver-recorder/upload-file`,

                parallelChunkUploads: false,
                chunking: true,
                chunkSize: 10000000,

                maxFiles: 1,
                maxFilesize: 3 * 1024 * 1024,
                uploadMultiple: false,

                acceptedFiles: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                previewTemplate: this.template(),
            },

            dropzoneImageOptions: {
                method: 'POST',
                headers: { 'Authorization': this.$store.getters.token },
                url: `${window.origin}/api/driver-recorder/upload-file`,

                parallelChunkUploads: false,
                chunking: true,
                chunkSize: 10000000,

                maxFiles: 1,
                maxFilesize: 20 * 1024 * 1024,
                uploadMultiple: false,

                acceptedFiles: 'image/png, image/jpg, image/jpeg, application/pdf',

                previewTemplate: this.template(),
            },

            attached_image_1_id: '',
            attached_image_1_path: '',
            attached_image_1_is_pdf: false,
            attached_pdf_1_path: '',

            attached_image_2_id: '',
            attached_image_2_path: '',
            attached_image_2_is_pdf: false,
            attached_pdf_2_path: '',

            attached_image_3_id: '',
            attached_image_3_path: '',
            attached_image_3_is_pdf: false,
            attached_pdf_3_path: '',

            attached_image_4_id: '',
            attached_image_4_path: '',
            attached_image_4_is_pdf: false,
            attached_pdf_4_path: '',

            attached_image_5_id: '',
            attached_image_5_path: '',
            attached_image_5_is_pdf: false,
            attached_pdf_5_path: '',

            attached_image_6_id: '',
            attached_image_6_path: '',
            attached_image_6_is_pdf: false,
            attached_pdf_6_path: '',

            attached_image_7_id: '',
            attached_image_7_path: '',
            attached_image_7_is_pdf: false,
            attached_pdf_7_path: '',

            attached_image_8_id: '',
            attached_image_8_path: '',
            attached_image_8_is_pdf: false,
            attached_pdf_8_path: '',

            attached_image_9_id: '',
            attached_image_9_path: '',
            attached_image_9_is_pdf: false,
            attached_pdf_9_path: '',

            attached_image_10_id: '',
            attached_image_10_path: '',
            attached_image_10_is_pdf: false,
            attached_pdf_10_path: '',

            attached_file_id: '',
            attached_file_path: '',
            attached_file_name: '',
            attached_file_size: '',

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
        environment() {
            return process.env.MIX_APP_ENV;
        },
    },
    created() {
        this.handleValidatePermission();

        this.initData();

        this.$bus.on('HAS_PROCESS_RUNNING', (status) => {
            this.hasProcessRunning = status;
        });

        this.$bus.on('DELETE_UPLOAD_BLOCK', this.handleDeleteUploadBlock);
        this.$bus.on('ACTION_CHANGE_UPLOAD_DATA', this.handleActionChangeUploadData);
        this.$bus.on('POST_DATA', this.handleProcessUploadDataFormat);
        this.$bus.on('REMOVE_POST_DATA', this.handleProcessRemoveUploadData);
    },
    destroyed() {
        this.$bus.off('DELETE_UPLOAD_BLOCK', this.handleDeleteUploadBlock);
        this.$bus.off('ACTION_CHANGE_UPLOAD_DATA', this.handleActionChangeUploadData);
        this.$bus.off('POST_DATA', this.handleProcessUploadDataFormat);
        this.$bus.on('REMOVE_POST_DATA', this.handleProcessRemoveUploadData);
    },
    methods: {
        template() {
            return `<div></div>`;
        },
        async initData() {
            try {
                this.overlay.show = true;

                await this.handleGetListDepartment();
                await this.handleGetListUserByDepartment();
                await this.handleGetDetailFile();

                this.overlay.show = false;
            } catch (err) {
                console.log('INIT_DATA_ERROR');
                console.log(err);
            }
        },
        async handleGetDetailFile() {
            try {
                const URL = `${urlAPI.apiGetDetailFile}/${this.$route.params.id}`;

                const response = await getDetailFile(URL);

                const DATA = [];

                const UPLOAD_DATA = [];

                if (response.code === 200) {
                    this.items = response.data;

                    const RECORDER_IMAGES = response.data.driver_recorder_images;

                    const EXCEL_FILE_INFO = response.data.excel;

                    this.isForm.record_date = this.items.record_date;
                    this.isForm.department = this.items.department_id;
                    this.isForm.crew_member = this.items.crew_member_id;
                    this.isForm.title = this.items.title;
                    this.isForm.remark = this.items.remark;
                    this.isForm.type_one = this.items.type_one || null;
                    this.isForm.type_two = this.items.type_two || null;
                    this.isForm.shipper = this.items.shipper || null;
                    this.isForm.accident_classification = this.items.accident_classification || null;
                    this.isForm.place_of_occurrence = this.items.place_of_occurrence || null;
                    this.isForm.is_create_with_notice = this.items.flag_send_noti ? 'accepted' : 'not_accepted';
                    this.isForm.flag_send_noti = this.items.flag_send_noti;
                    this.isForm.driver_recorder_id = this.items.id;

                    for (let i = 0; i < this.items.list_recorder.length; i++) {
                        DATA.push({
                            id: i,
                            title: this.items.list_recorder[i].movie_title,
                            list_movie: {},
                        });

                        UPLOAD_DATA.push({
                            id: i,
                            movie_title: this.items.list_recorder[i].movie_title,
                            list_movie: {},
                        });

                        if (this.items.list_recorder[i].front) {
                            DATA[i].list_movie.front = {
                                id: this.items.list_recorder[i].front.id,
                                fileName: this.items.list_recorder[i].front.file_name,
                                isCompleted: true,
                                progressBandwidth: '',
                                progressPercentage: 100,
                            };

                            UPLOAD_DATA[i].list_movie.front = this.items.list_recorder[i].front.id;
                        } else {
                            DATA[i].list_movie.front = {
                                id: '',
                                fileName: '',
                                isCompleted: false,
                                progressBandwidth: '',
                                progressPercentage: '',
                            };
                        }

                        if (this.items.list_recorder[i].inside) {
                            DATA[i].list_movie.inside = {
                                id: this.items.list_recorder[i].inside.id,
                                fileName: this.items.list_recorder[i].inside.file_name,
                                isCompleted: true,
                                progressBandwidth: '',
                                progressPercentage: 100,
                            };

                            UPLOAD_DATA[i].list_movie.inside = this.items.list_recorder[i].inside.id;
                        } else {
                            DATA[i].list_movie.inside = {
                                id: '',
                                fileName: '',
                                isCompleted: false,
                                progressBandwidth: '',
                                progressPercentage: '',
                            };
                        }

                        if (this.items.list_recorder[i].behind) {
                            DATA[i].list_movie.behind = {
                                id: this.items.list_recorder[i].behind.id,
                                fileName: this.items.list_recorder[i].behind.file_name,
                                isCompleted: true,
                                progressBandwidth: '',
                                progressPercentage: 100,
                            };

                            UPLOAD_DATA[i].list_movie.behind = this.items.list_recorder[i].behind.id;
                        } else {
                            DATA[i].list_movie.behind = {
                                id: '',
                                fileName: '',
                                isCompleted: false,
                                progressBandwidth: '',
                                progressPercentage: '',
                            };
                        }
                    }

                    this.sampleUploadData = JSON.parse(JSON.stringify(DATA));

                    this.isForm.listUploadFile = JSON.parse(JSON.stringify(UPLOAD_DATA));

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

            this.eventAddTableUploadFile();
        },
        eventAddTableUploadFile() {
            if (this.sampleUploadData.length > 9) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: 'ファイルは10個以内でアップロードしてください',
                });
            } else {
                this.sampleUploadData.push({
                    hasProcessRunning: false,
                    id: this.sampleUploadData.length === 0 ? 0 : this.sampleUploadData.length,
                    movie_title: '',
                    list_movie: {
                        front: {
                            id: '',
                            filePath: null,
                            progressPercentage: 0,
                            progressBandwidth: 0,
                            fileName: '',
                            isCompleted: false,
                        },
                        inside: {
                            id: '',
                            filePath: null,
                            progressPercentage: 0,
                            progressBandwidth: 0,
                            fileName: '',
                            isCompleted: false,
                        },
                        behind: {
                            id: '',
                            filePath: null,
                            progressPercentage: 0,
                            progressBandwidth: 0,
                            fileName: '',
                            isCompleted: false,
                        },
                    },
                });

                this.isForm.listUploadFile.push({
                    id: this.isForm.listUploadFile.length === 0 ? 0 : this.isForm.listUploadFile.length,
                    movie_title: '',
                    list_movie: {
                        front: '',
                        inside: '',
                        behind: '',
                    },
                });
            }
        },
        eventDeleteTableUploadFile(idx) {
            if (idx === null || idx === -1) {
                return;
            }

            if (idx >= 0 && idx < this.sampleUploadData.length) {
                this.sampleUploadData.splice(idx, 1);
            }

            this.sampleUploadData.forEach((item, index) => {
                item.id = index;
            });

            for (let i = 0; i < this.isForm.listUploadFile.length; i++) {
                if (idx === this.isForm.listUploadFile[i].id) {
                    const INDEX = this.isForm.listUploadFile.indexOf(this.isForm.listUploadFile[i]);
                    this.isForm.listUploadFile.splice(INDEX, 1);
                }
            }
        },
        handleValidateFormData() {
            let isValid = false;

            if (!this.isForm.record_date) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: this.$t('DRIVER_RECORDER_REGISTER.VALIDATION_MESSAGE.RECORD_DATE'),
                });
            } else if (!this.isForm.department) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: this.$t('DRIVER_RECORDER_REGISTER.VALIDATION_MESSAGE.DEPARTMENT'),
                });
            } else if (this.isForm.title.length === 0 || this.isForm.title.length > 20) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: this.$t('DRIVER_RECORDER_REGISTER.VALIDATION_MESSAGE.TITLE'),
                });
            } else if (this.isForm.remark && this.isForm.remark.length > 1000) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: this.$t('DRIVER_RECORDER_REGISTER.VALIDATION_MESSAGE.REMARK_MAX_LENGTH'),
                });
            } else if (this.isForm.type_one === null) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '事故GP is required.',
                });
            } else if (this.isForm.type_two === null) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '有責無責 is required.',
                });
            } else if (this.isForm.shipper === null) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '荷主 is required.',
                });
            } else if (this.isForm.accident_classification === null) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '事故区分 is required.',
                });
            } else if (this.isForm.place_of_occurrence === null) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '発生場所 is required.',
                });
            } else {
                isValid = true;
            }

            return isValid;
        },
        handleProcessUploadDataFormat(DATA) {
            const COPY_LIST = JSON.parse(JSON.stringify(DATA));

            if (COPY_LIST) {
                for (let i = 0; i < COPY_LIST.length; i++) {
                    const MOVIE_TITLE = COPY_LIST[i].title;
                    COPY_LIST[i].movie_title = MOVIE_TITLE || '';

                    delete COPY_LIST[i].hasProcessRunning;
                    delete COPY_LIST[i].title;

                    const FRONT_ID = COPY_LIST[i].list_movie.front.id;
                    const INSIDE_ID = COPY_LIST[i].list_movie.inside.id;
                    const BEHIND_ID = COPY_LIST[i].list_movie.behind.id;

                    COPY_LIST[i].list_movie['front'] = FRONT_ID;
                    COPY_LIST[i].list_movie['inside'] = INSIDE_ID;
                    COPY_LIST[i].list_movie['behind'] = BEHIND_ID;

                    if (!COPY_LIST[i].list_movie['front']) {
                        delete COPY_LIST[i].list_movie['front'];
                    }

                    if (!COPY_LIST[i].list_movie['inside']) {
                        delete COPY_LIST[i].list_movie['inside'];
                    }

                    if (!COPY_LIST[i].list_movie['behind']) {
                        delete COPY_LIST[i].list_movie['behind'];
                    }
                }
            }

            this.isForm.listUploadFile = COPY_LIST;
        },
        handleProcessRemoveUploadData(id, position) {
            for (let i = 0; i < this.sampleUploadData.length; i++) {
                if (id === this.sampleUploadData[i].id) {
                    this.sampleUploadData[i].list_movie[position].id = '';
                    this.sampleUploadData[i].list_movie[position].fileName = '';
                    this.sampleUploadData[i].list_movie[position].isCompleted = false;
                    this.sampleUploadData[i].list_movie[position].progressBandwidth = 0;
                    this.sampleUploadData[i].list_movie[position].progressPercentage = 0;
                }
            }

            for (let i = 0; i < this.isForm.listUploadFile.length; i++) {
                if (id === this.isForm.listUploadFile[i].id) {
                    delete this.isForm.listUploadFile[i].list_movie[position];
                }
            }
        },
        handleActionChangeUploadData(DATA) {
            if (DATA) {
                this.sampleUploadData = DATA;
            }
        },
        onClickBack() {
            this.$router.push({ name: 'DriverRecorderDetail' });
        },
        async onClickSave() {
            this.overlay.show = true;

            if (this.handleValidateFormData()) {
                for (let i = 0; i < this.isForm.listUploadFile.length; i++) {
                    delete this.isForm.listUploadFile[i].id;
                }

                try {
                    const URL = `${urlAPI.apiSaveFile}/${this.$route.params.id}`;

                    const DATA = {
                        record_date: this.handleConvertTime(this.isForm.record_date),
                        department_id: this.isForm.department,
                        crew_member_id: this.isForm.crew_member,
                        title: this.isForm.title,
                        remark: this.isForm.remark,
                        list_recorder: this.isForm.listUploadFile,
                        recorder_images: [],
                        excel_file_id: null,
                        type_one: this.isForm.type_one,
                        type_two: this.isForm.type_two,
                        shipper: this.isForm.shipper,
                        accident_classification: this.isForm.accident_classification,
                        place_of_occurrence: this.isForm.place_of_occurrence,
                        is_draft: this.isForm.is_create_with_notice === 'accepted' ? 1 : 0,
                        flag_send_noti: this.isForm.is_create_with_notice === 'accepted' ? 1 : 0,
                    };

                    if (this.attached_image_1_id) {
                        DATA.recorder_images.push(this.attached_image_1_id);
                    }

                    if (this.attached_image_2_id) {
                        DATA.recorder_images.push(this.attached_image_2_id);
                    }

                    if (this.attached_image_3_id) {
                        DATA.recorder_images.push(this.attached_image_3_id);
                    }

                    if (this.attached_image_4_id) {
                        DATA.recorder_images.push(this.attached_image_4_id);
                    }

                    if (this.attached_image_5_id) {
                        DATA.recorder_images.push(this.attached_image_5_id);
                    }

                    if (this.attached_image_6_id) {
                        DATA.recorder_images.push(this.attached_image_6_id);
                    }

                    if (this.attached_image_7_id) {
                        DATA.recorder_images.push(this.attached_image_7_id);
                    }

                    if (this.attached_image_8_id) {
                        DATA.recorder_images.push(this.attached_image_8_id);
                    }

                    if (this.attached_image_9_id) {
                        DATA.recorder_images.push(this.attached_image_9_id);
                    }

                    if (this.attached_image_10_id) {
                        DATA.recorder_images.push(this.attached_image_10_id);
                    }

                    if (this.attached_file_id) {
                        DATA.excel_file_id = this.attached_file_id;
                    }

                    const response = await putUploadData(URL, DATA);

                    console.log('=============================================================');
                    console.log('[1378] -> response ==>', response);
                    console.log('=============================================================');

                    if (response.code === 200) {
                        MakeToast({
                            variant: 'success',
                            title: this.$t('SUCCESS'),
                            content: '編集が完了しました',
                        });

                        if (this.isForm.is_create_with_notice === 'accepted' && this.isForm.flag_send_noti !== 1) {
                            await this.handleCreateNotification(this.$route.params.id);
                        }

                        this.onClickBack();
                    } else {
                        MakeToast({
                            variant: 'warning',
                            title: this.$t('WARNING'),
                            content: response.message,
                        });
                    }
                } catch (error) {
                    console.log(error);
                }
            }

            this.overlay.show = false;
        },
        handleDeleteUploadBlock(id) {
            for (let i = 0; i < this.isForm.listUploadFile.length; i++) {
                if (this.isForm.listUploadFile[i].id === id) {
                    this.isForm.listUploadFile.splice(i, 1);
                }
            }
        },
        handleValidatePermission() {
            if (!this.hasAccess.includes(this.role)) {
                this.$router.push({ name: 'ErrorPages' }).catch(() => {});
            }
        },
        handlePreviewFile() {
            const URL = this.attached_file_path;
            window.location.href = URL;
        },
        afterAdded(file, target) {
            const reader = new FileReader();

            let max_file_size = 3 * 1024 * 1024;

            if (target !== 11) {
                max_file_size = 20 * 1024 * 1024;
            }

            if (file.size > max_file_size) {
                return;
            } else {
                reader.onload = () => {
                    if (target === 11) {
                        this.attached_file_name = file.name;
                        this.attached_file_size = this.handleRenderFileSize(file.size);
                    }
                };

                reader.readAsDataURL(file);
            }
        },
        handleRenderFileSize(sizeInBytes) {
            const sizeInMB = sizeInBytes / 1024 / 1024;
            return sizeInMB.toFixed(2);
        },
        afterComplete(response, target) {
            let max_file_size = 3 * 1024 * 1024;

            if (target !== 11) {
                max_file_size = 20 * 1024 * 1024;
            }

            if (response.size > max_file_size) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: `${target === 11 ? '3' : '20'}MB以下のファイルのみアップロードが可能です`,
                });
            } else {
                if (response.xhr.status === 201) {
                    const DATA = JSON.parse(response.xhr.response);

                    if (response.type === 'application/pdf') {
                        if (target === 1) {
                            this.attached_image_1_id = DATA.id;
                            this.attached_pdf_1_path = URL.createObjectURL(response);
                            this.attached_image_1_is_pdf = true;
                        } else if (target === 2) {
                            this.attached_image_2_id = DATA.id;
                            this.attached_pdf_2_path = URL.createObjectURL(response);
                            this.attached_image_2_is_pdf = true;
                        } else if (target === 3) {
                            this.attached_image_3_id = DATA.id;
                            this.attached_pdf_3_path = URL.createObjectURL(response);
                            this.attached_image_3_is_pdf = true;
                        } else if (target === 4) {
                            this.attached_image_4_id = DATA.id;
                            this.attached_pdf_4_path = URL.createObjectURL(response);
                            this.attached_image_4_is_pdf = true;
                        } else if (target === 5) {
                            this.attached_image_5_id = DATA.id;
                            this.attached_pdf_5_path = URL.createObjectURL(response);
                            this.attached_image_5_is_pdf = true;
                        } else if (target === 6) {
                            this.attached_image_6_id = DATA.id;
                            this.attached_pdf_6_path = URL.createObjectURL(response);
                            this.attached_image_6_is_pdf = true;
                        } else if (target === 7) {
                            this.attached_image_7_id = DATA.id;
                            this.attached_pdf_7_path = URL.createObjectURL(response);
                            this.attached_image_7_is_pdf = true;
                        } else if (target === 8) {
                            this.attached_image_8_id = DATA.id;
                            this.attached_pdf_8_path = URL.createObjectURL(response);
                            this.attached_image_8_is_pdf = true;
                        } else if (target === 9) {
                            this.attached_image_9_id = DATA.id;
                            this.attached_pdf_9_path = URL.createObjectURL(response);
                            this.attached_image_9_is_pdf = true;
                        } else if (target === 10) {
                            this.attached_image_10_id = DATA.id;
                            this.attached_pdf_10_path = URL.createObjectURL(response);
                            this.attached_image_10_is_pdf = true;
                        } else {
                            console.log('[ERROR]');
                        }
                    } else {
                        if (target === 11) {
                            this.attached_file_id = DATA.id;
                            this.attached_file_path = DATA.file_url;
                        } else if (target === 1) {
                            this.attached_image_1_id = DATA.id;
                            this.attached_image_1_path = DATA.file_url;
                        } else if (target === 2) {
                            this.attached_image_2_id = DATA.id;
                            this.attached_image_2_path = DATA.file_url;
                        } else if (target === 3) {
                            this.attached_image_3_id = DATA.id;
                            this.attached_image_3_path = DATA.file_url;
                        } else if (target === 4) {
                            this.attached_image_4_id = DATA.id;
                            this.attached_image_4_path = DATA.file_url;
                        } else if (target === 5) {
                            this.attached_image_5_id = DATA.id;
                            this.attached_image_5_path = DATA.file_url;
                        } else if (target === 6) {
                            this.attached_image_6_id = DATA.id;
                            this.attached_image_6_path = DATA.file_url;
                        } else if (target === 7) {
                            this.attached_image_7_id = DATA.id;
                            this.attached_image_7_path = DATA.file_url;
                        } else if (target === 8) {
                            this.attached_image_8_id = DATA.id;
                            this.attached_image_8_path = DATA.file_url;
                        } else if (target === 9) {
                            this.attached_image_9_id = DATA.id;
                            this.attached_image_9_path = DATA.file_url;
                        } else if (target === 10) {
                            this.attached_image_10_id = DATA.id;
                            this.attached_image_10_path = DATA.file_url;
                        } else {
                            console.log('[ERROR]');
                        }
                    }
                }
            }
        },
        handleRemoveFile(target) {
            if (target === 11) {
                this.attached_file_id = null;
                this.attached_file_path = '';
                this.attached_file_name = '';
                this.attached_file_size = '';
            } else if (target === 1) {
                this.attached_image_1_id = null;
                this.attached_image_1_path = '';
                this.attached_image_1_is_pdf = false;
                this.attached_pdf_1_path = '';
            } else if (target === 2) {
                this.attached_image_2_id = null;
                this.attached_image_2_path = '';
                this.attached_image_2_is_pdf = false;
                this.attached_pdf_2_path = '';
            } else if (target === 3) {
                this.attached_image_3_id = null;
                this.attached_image_3_path = '';
                this.attached_image_3_is_pdf = false;
                this.attached_pdf_3_path = '';
            } else if (target === 4) {
                this.attached_image_4_id = null;
                this.attached_image_4_path = '';
                this.attached_image_4_is_pdf = false;
                this.attached_pdf_4_path = '';
            } else if (target === 5) {
                this.attached_image_5_id = null;
                this.attached_image_5_path = '';
                this.attached_image_5_is_pdf = false;
                this.attached_pdf_5_path = '';
            } else if (target === 6) {
                this.attached_image_6_id = null;
                this.attached_image_6_path = '';
                this.attached_image_6_is_pdf = false;
                this.attached_pdf_6_path = '';
            } else if (target === 7) {
                this.attached_image_7_id = null;
                this.attached_image_7_path = '';
                this.attached_image_7_is_pdf = false;
                this.attached_pdf_7_path = '';
            } else if (target === 8) {
                this.attached_image_8_id = null;
                this.attached_image_8_path = '';
                this.attached_image_8_is_pdf = false;
                this.attached_pdf_8_path = '';
            } else if (target === 9) {
                this.attached_image_9_id = null;
                this.attached_image_9_path = '';
                this.attached_image_9_is_pdf = false;
                this.attached_pdf_9_path = '';
            } else if (target === 10) {
                this.attached_image_10_id = null;
                this.attached_image_10_path = '';
                this.attached_image_10_is_pdf = false;
                this.attached_pdf_10_path = '';
            } else {
                console.log('[ERROR]');
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
        handleRenderColor(option) {
            if (option === 1 || option === '1') {
                return 'text-red';
            } else {
                return '';
            }
        },
        handleConvertTime(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) {
                month = '0' + month;
            }

            if (day.length < 2) {
                day = '0' + day;
            }

            return [year, month, day].join('-');
        },
        async handleCreateNotification(driver_recorder_id) {
            try {
                const formData = new FormData();

                const departmentName = this.listDepartment.find(item => item.id === this.isForm.department);
                const title = `【原因対策】${this.handleConvertTime(this.isForm.record_date)} - ${departmentName?.department_name} - ${this.isForm.title}`;

                const content = `今回発生した事故GPの映像・写真・速報を確認し、あなたが思う発生原因と対策方法について回答記述して下さい。\n\n発生詳細の確認はこちらから↓`;

                const surveys = [
                    { id: 0, question_content: '【原因】', type: 3, answer: [] },
                    {
                        id: 1, question_content: '【原因】', type: 1, answer: [
                            { position: 0, answer_content: '認知ミス' },
                            { position: 1, answer_content: '判断ミス' },
                            { position: 2, answer_content: '操作ミス' },
                            { position: 3, answer_content: 'その他ミス' },
                        ],
                    },
                    { id: 2, question_content: '【対策】', type: 3, answer: [] },
                    { id: 3, question_content: '【その他今回の件について感じた事】(任意入力)', type: 4, answer: [] },
                ];

                formData.append('subject', title);
                formData.append('content', content);
                formData.append('is_draft', 2);
                formData.append('surveys', JSON.stringify(surveys));
                formData.append('driver_recorder_id', driver_recorder_id);

                let url = '';

                if (this.environment === 'local' || this.environment === 'dev') {
                    url = 'https://izumi-web-app.vw-dev.com/api/notices';
                } else if (this.environment === 'staging') {
                    url = 'https://izumi-web-app-stage.izumilogi.com/api/notices';
                } else if (this.environment === 'production') {
                    url = 'https://izumi-web-app.izumilogi.com/api/notices';
                }

                const token = this.$store.getters.token;

                const response = await axios.post(url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        'Authorization': token,
                    },
                });

                if (response.code === 200) {
                    MakeToast({
                        variant: 'success',
                        title: '成功',
                        content: '新しいお知らせを正常に作成します。',
                    });
                }
            } catch (error) {
                console.log(error);
            }
        },
        handleCheckIfIsManagerRole() {
            const MANAGER_ROLES = ['department_manager', 'executive_officer', 'director', 'dx_user', 'dx_manager', 'site_manager', 'hq_manager', 'quality_control'];

            if (MANAGER_ROLES.includes(this.role)) {
                return true;
            }

            return false;
        },
        handleTrimSpace(string) {
            if (string) {
                return string.replace(/[\s\u3000]/g, '');
            } else {
                return string;
            }
        },
        async handleGetListUserByDepartment() {
            if (this.isForm.department) {
                try {
                    this.isForm.crew_member = null;
                    this.listCrewMemberByDepartment = [];

                    const url = `${urlAPI.apiGetListUserByDepartment}/${this.isForm.department}`;

                    const response = await getListUserByDepartment(url);

                    const { code, data } = response;

                    if (code && code === 200) {
                        if (data.length) {
                            const result = [];

                            data.forEach((user) => {
                                result.push(
                                    {
                                        value: user.id,
                                        text: this.handleTrimSpace(user.name),
                                    }
                                );
                            });

                            this.listCrewMemberByDepartment = [...result];
                        }
                    }
                } catch (error) {
                    console.error('[handleGetListUserByDepartment] ==> ', error);
                }
            }
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables';

.text-red {
    color: red !important;
}

.pdf-holder {
    &:hover {
        opacity: .4;
        cursor: pointer;
    }
}

::v-deep .vue-pdf-embed__page {
    &>canvas {
        width: 190px !important;
        height: 190px !important;
        object-fit: cover;
        object-position: center;
    }
}

.driver-recorder-edit {
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

    &:hover {
        opacity: .6;
        cursor: pointer;
    }
}

.upload-img-card {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    width: 200px;
    height: 200px;
    border: 2px dashed #dee2e6;

    &>span {
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
    position: relative;
    flex-direction: row;

    &>i {
        top: -10px;
        left: 185px;
        color: red;
        position: absolute;

        &:hover {
            opacity: .6;
            cursor: pointer;
        }
    }
}

.dropzone-custom-content {
    &:hover {
        opacity: .6;
        cursor: pointer;
    }

    &>div {
        padding: 10px 0px;
        border: 2px dashed #dee2e6;
    }
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

    .icon-preview {
        position: absolute;
        top: -10px;
        color: red;

        &:hover {
            opacity: .6;
            cursor: pointer;
        }
    }
}

.d-flex-row {
    display: flex;
    flex-direction: row;
}

@media only screen and (max-width: 768px) {
    .card-holder {
        width: 100%;
        display: flex;
        margin-bottom: 20px;
        align-items: center;
        flex-direction: column;

        &>div {
            width: 100%;
        }

        &>i {
            left: 275px;
        }
    }

    .upload-img-card {
        width: 100%;
        height: auto;
        padding: 10px 0px;
    }

    .image-card {
        width: 190px;
        height: 190px;
        object-fit: cover;
    }

    .d-flex-row {
        display: flex;
        flex-direction: column;
    }
}
</style>
