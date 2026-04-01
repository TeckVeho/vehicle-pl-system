<template>
	<b-overlay :show="overlay.show" :blur="overlay.blur" :rounded="overlay.sm" :variant="overlay.variant" :opacity="overlay.opacity">
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p class="text-loading">{{ $t('PLEASE_WAIT') }}</p>
			</div>
		</template>

		<div class="driver-recorder-list">
			<div class="driver-recorder-list__title-header">
				<vHeaderPage>{{ $t('PAGE_TITLE.DRIVER_RECONRDER_LIST') }}</vHeaderPage>
			</div>

			<div class="driver-recorder-list__filter">
				<FilterListDriverRecorder :list-department="listDepartment" :list-type="listType" />
			</div>

			<div class="driver-recorder-list__select-year-month">
				<b-row>
					<b-col class="col-sm-6 mb-3">
						<div class="date-selector">
							<b-button-group>
								<b-button class="minus-btn" @click="minus()">
									<i class="fas fa-caret-left" />
								</b-button>

								<b-button class="date">
									<span>{{ date }}</span>
								</b-button>

								<b-button class="plus-btn" @click="plus()">
									<i class="fas fa-caret-right" />
								</b-button>

								<date-picker
									v-model="year_month_picker"
									type="month"
									format="YYYY-MM"
									:lang="ja_locale"
									value-type="format"
									:disabled-date="disabledDate"
									:clearable="false"
									@input="handleChangeInput"
								/>
							</b-button-group>
						</div>
					</b-col>
					<b-col class="col-sm-6">
						<vButton
							v-if="hasAccessRegister.includes(role)"
							:text-button="$t('DRIVER_RECORDER.BUTTON_UPLOAD')"
							:class-name="'btn-radius v-button-default btn-registration'"
							@click.native="onClickUpload()"
						/>

						<vButton
							:text-button="'プレイリスト'"
							class="mr-3"
							:class-name="'btn-radius v-button-default btn-registration'"
							@click.native="handleShowModalPlaylistDetail()"
						/>

						<!-- <vButton
							:text-button="'モザイク処理'"
							class="mr-3"
							:class-name="'btn-radius v-button-default btn-registration'"
							@click.native="handleTurnOnModalMosaicVideo()"
							/> -->
					</b-col>
				</b-row>
			</div>

			<div class="driver-recorder-list__table-data">
				<b-table
					id="table-driver-recorder"
					show-empty
					striped
					hover
					responsive
					bordered
					no-local-sorting
					no-sort-reset
					no-border-collapse
					:fields="headerTable"
					:items="items"
					@sort-changed="handleSort"
				>
					<template #cell(play)="data">
						<i :id="`show-list-play-${data.item.id}`" class="fas fa-tv-alt" />

						<b-popover :target="`show-list-play-${data.item.id}`" triggers="hover">
							<template #title>
								<div class="text-center">
									<b>{{ $t('DRIVER_RECORDER_INDEX.VIDEO_PLAYLIST') }}</b>
								</div>
							</template>

							<div
								v-for="(video, videoIndex) in data.item.action_camera"
								:key="videoIndex"
								:class="['item-play', videoIndex === 0 ? 'first' : '']"
								@click="handlePlayRecorder(data.item.id, videoIndex)"
							>
								<span class="'item-play-text'">{{ video.movie_title }}</span>
							</div>
						</b-popover>
					</template>

					<template #cell(record_date)="data">
						<span>{{ data.item.accident_date }}</span>
					</template>

					<template #cell(department_id)="data">
						<span>{{ data.item.department_name }}</span>
					</template>

					<template #cell(type_one)="data">
						<span>{{ convertType(data.item.type_one, 1) }}</span>
					</template>

					<template #cell(type_two)="data">
						<span :class="handleRenderClass(data.item.type_two)">{{ convertType(data.item.type_two, 2) }}</span>
					</template>

					<template #cell(shipper)="data">
						<span>{{ convertType(data.item.shipper, 3) }}</span>
					</template>

					<template #cell(accident_classification)="data">
						<span>{{ convertType(data.item.accident_classification, 4) }}</span>
					</template>

					<template #cell(place_of_occurrence)="data">
						<span>{{ convertType(data.item.place_of_occurrence, 5) }}</span>
					</template>

					<template #cell(download)="detail">
						<i class="fas fa-arrow-to-bottom" @click="onClickDownload(detail.item.id)" />
					</template>

					<template #cell(playlist)="playlist">
						<i class="fas fa-list-music" @click="handleShowModalPlaylistIndex(playlist.item.id)" />
					</template>

					<template #cell(detail)="detail">
						<i class="fas fa-eye" @click="onClickDetail(detail.item.id)" />
					</template>

					<template #cell(delete)="data">
						<i class="fas fa-trash" @click="() => { tempID = data.item.id; showModal = true; }" />
					</template>

					<template #empty="">
						<span>{{ $t('TABLE_EMPTY') }}</span>
					</template>
				</b-table>
			</div>

			<div class="driver-recorder-list__pagination">
				<div class="select-per-page text-left">
					<div>
						<label for="per-page">1ページ毎の表示数</label>
					</div>
					<b-form-select
						id="per-page"
						v-model="pagination.per_page"
						:options="optionsPerPage"
						size="sm"
					/>
				</div>

				<div v-if="pagination.total_rows > 20" class="show-pagination">
					<vPagination
						:aria-controls="'table-employee-master-list'"
						:current-page="pagination.current_page"
						:per-page="pagination.per_page"
						:total-rows="pagination.total_rows"
						:next-class="'next'"
						:prev-class="'prev'"
						@currentPageChange="getCurrentPage"
					/>
				</div>
			</div>

			<b-modal id="modal-cf" v-model="showModal" no-close-on-backdrop no-close-on-esc hide-header :static="true" header-class="modal-custom-header" content-class="modal-custom-body" footer-class="modal-custom-footer">
				<template #default>
					<span>このデータを削除してもよろしいですか？</span>
				</template>

				<template #modal-footer>
					<b-button class="modal-btn btn-cancel" :disabled="overlay.show" @click="showModal = false">
						{{ $t("NO") }}
					</b-button>

					<b-button class="modal-btn btn-apply" :disabled="overlay.show" @click="handleDeleteFile()">
						{{ $t("YES") }}
					</b-button>
				</template>
			</b-modal>

			<b-modal v-model="showModalPlaylistCreate" header-class="modal-create-playlist-header" size="lg" centered>
				<template #modal-header>
					<div class="modal-header-card">
						<span>プレイリストを登録</span>
						<i class="fas fa-times" @click="showModalPlaylistCreate = false" />
					</div>
				</template>

				<template #default>
					<div class="d-flex w-100 flex-column card-holder">
						<label for="playlist-title">プレイリスト名</label>
						<b-form-input v-model="playlist_title" />

						<label for="playlist-thumbnail" class="mt-3">トップ画像</label>
						<template v-if="playlist_thumbnail.url">
							<div class="d-flex w-100 flex-column justify-content-center align-items-center">
								<img :src="playlist_thumbnail.url" alt="" class="image-card">
								<span class="remove-file mt-3" @click="handleRemoveFile()">ファイルを削除する</span>
							</div>
						</template>

						<template v-else>
							<vuedropzone
								id="customdropzoneimage3"
								ref="myVueDropzoneImage3"
								:use-custom-slot="true"
								:include-styling="false"
								:options="dropzoneImageOptions"
								@vdropzone-file-added="afterAdded($event, 'playlist_thumbnail')"
								@vdropzone-complete="afterComplete($event, 'playlist_thumbnail')"
							>
								<div class="upload-img-card">
									<div class="d-row flex-row text-center">
										<i class="fa fa-cloud-upload" />
										<span>アップロード</span>
									</div>
								</div>
							</vuedropzone>
						</template>
					</div>
				</template>

				<template #modal-footer>
					<div class="d-flex w-100 justify-content-end">
						<vButton
							:text-button="'作成'"
							:class-name="'btn-radius v-button-default btn-registration'"
							@click.native="handleCreatePlayList()"
						/>
					</div>
				</template>
			</b-modal>

			<b-modal v-model="showModalPlaylistIndex" header-class="modal-show-playlist-header" size="lg" centered>
				<template #modal-header>
					<div class="modal-header-card">
						<span>プレイリストに追加</span>
						<i class="fas fa-times" @click="showModalPlaylistIndex = false" />
					</div>
				</template>

				<template #default>
					<vButton
						:text-button="'新規プレイリスト'"
						:class-name="'btn-radius v-button-default btn-registration'"
						@click.native="handleShowModalPlaylistCreate()"
					/>

					<template v-if="playlist_items.length">
						<div v-for="(playlist, index) in playlist_items" :key="index" class="d-flex flex-row w-100 mt-3" style="padding: 0px 20px;">
							<img v-if="playlist['image_file']" :src="playlist['image_file']['file_url']" alt="" class="playlist-img">
							<img v-else :src="'http://thaibinhtv.vn/thumb/640x400/assets/images/imgstd.jpg'" alt="" class="playlist-img">

							<div class="info-card ml-3">
								<span v-if="playlist['name']">{{ playlist['name'] }}</span>

								<div
									class="circle"
									:style="playlist.status ? 'background-color: #82CD47' : 'background-color: #FFFFFF'"
									@click="handleChangePlaylistStatus(playlist['id'], index)"
								>
									<i class="fas fa-check" />
								</div>
							</div>
						</div>
					</template>

					<template v-else>
						<div class="d-flex w-100 justify-content-center align-items-center">
							<span>プレイリストがありません。</span>
						</div>
					</template>
				</template>

				<template #modal-footer>
					<div v-if="playlist_items.length" class="d-flex w-100 justify-content-end">
						<vButton
							:text-button="'完了'"
							:class-name="'btn-radius v-button-default btn-registration'"
							@click.native="handleSavePlaylistIndex()"
						/>
					</div>

					<div v-else />
				</template>
			</b-modal>

			<b-modal v-model="showModalPlaylistDetail" header-class="modal-detail-playlist-header" size="lg" centered hide-footer>
				<template #modal-header>
					<div class="modal-header-card">
						<span>プレイリスト</span>
						<i class="fas fa-times" @click="showModalPlaylistDetail = false" />
					</div>
				</template>

				<template #default>
					<template v-if="playlist_items.length">
						<div v-for="(playlist, index) in playlist_items" :key="index" class="d-flex flex-row w-100 mt-3" style="padding: 0px 20px;">
							<img v-if="playlist['image_file']" :src="playlist['image_file']['file_url']" alt="" class="playlist-img">
							<img v-else :src="'http://thaibinhtv.vn/thumb/640x400/assets/images/imgstd.jpg'" alt="" class="playlist-img">

							<div class="info-card ml-3">
								<span v-if="playlist['name']">{{ playlist['name'] }}</span>

								<div class="d-flex flex-row justify-content-between">
									<b-button :id="`playlist-video-index-${index}`" class="gray-bg-button" @click="handleNavigateToPlaylistScreen(playlist['id'])">
										<i class="fas fa-tv-alt" />

										<b-popover :target="`playlist-video-index-${index}`" triggers="hover" placement="left" @show="setListTempDriverRecorder(index)">
											<template #title>
												<div class="text-center">
													<b>{{ $t('DRIVER_RECORDER_INDEX.VIDEO_PLAYLIST') }}</b>
												</div>
											</template>

											<draggable
												:list="list_temp_driver_recorder"
												handle=".handle"
												:animation="100"
												draggable=".drag-record"
												:class="drag ? 'cursor-grabbing' : ''"
												@start="drag=true"
												@end="drag=false"
												@change="dragChanged(index)"
											>
												<div
													v-for="(video, videoIndex) in list_temp_driver_recorder"
													:key="video.id"
													:class="['item-play handle drag-record', videoIndex === 0 ? 'first' : '']"
												>
													<span class="'item-play-text'">{{ video.title }}</span>
												</div>
											</draggable>
										</b-popover>
									</b-button>

									<b-button v-if="hasAccessDelete.includes(role)" class="ml-3 gray-bg-button" @click="handleOpenModalDeletePlaylist(index)">
										<i class="fas fa-trash" />
									</b-button>
								</div>
							</div>
						</div>
					</template>

					<template v-else>
						<div class="d-flex w-100 justify-content-center align-items-center">
							<span>プレイリストがありません。</span>
						</div>
					</template>
				</template>
			</b-modal>

			<b-modal v-model="showModalDeleteConfirm" centered size="lg" no-close-on-esc no-close-on-backdrop header-class="modal-detail-playlist-header" @hide="handleCloseModalDeleteConfirmation()">
				<template #modal-header>
					<span>削除</span>
				</template>

				<template #default>
					<span v-if="deleting_playlist_index !== null && playlist_items[deleting_playlist_index]">プレイリスト{{ playlist_items[deleting_playlist_index]['name'] }} を削除してもよろしいですか?</span>
				</template>

				<template #modal-footer>
					<div class="d-flex flex-row w-100 justify-content-end">
						<b-button class="cancel-delete-button" @click="showModalDeleteConfirm = false">
							<span>キャンセル</span>
						</b-button>

						<b-button class="confirm-delete-button ml-3" @click="handleDeletePlaylist()">
							<span>削除する</span>
						</b-button>
					</div>
				</template>
			</b-modal>

			<b-modal v-model="showModalMosaicVideo" size="xl" hide-footer centered static no-close-on-backdrop no-close-on-esc>
				<template #modal-header>
					<div class="modal-header-card">
						<span style="font-weight: bold;">モザイク処理</span>
						<i v-if="!is_processing_mosaic_video" class="fas fa-times" @click="showModalMosaicVideo = false" />
					</div>
				</template>

				<template #default>
					<div class="d-flex flex-column w-100" style="min-height: 150px;">
						<div class="d-flex flex-row justify-content-between align-items-center mb-3">
							<span>動画のモザイク処理を行います。</span>
						</div>

						<vuedropzone
							:use-custom-slot="true"
							id="customdropzonevideo"
							ref="myVueDropzoneVideo"
							:include-styling="false"
							@vdropzone-error="onError"
							@vdropzone-sending="onSending"
							:options="dropzoneVideoOptions"
							:disabled="is_processing_mosaic_video"
							@vdropzone-queue-complete="onQueueComplete"
							@vdropzone-file-added="afterAdded($event, 'video')"
							@vdropzone-complete="afterComplete($event, 'video')"
						>
							<div class="d-flex flex-row justify-content-center align-items-center dropzone-video-custom-content">
								<div class="d-row flex-row text-center">
									<template v-if="is_processing_mosaic_video">
										<b-icon icon="arrow-clockwise" animation="spin" font-scale="1" />
										<span>モザイク処理中...</span>
										<span>{{ processing_mosaic_video_percentage }}%</span>
									</template>

									<template v-else>
										<template v-if="is_uploading_origin_video">
											<span>動画をアップロード中です...</span>
											<b-icon icon="arrow-clockwise" animation="spin" font-scale="1" />
										</template>

										<template v-else>
											<template v-if="origin_video_file && origin_video_file.name">
												<span v-if="target_file_id">DEFACED: </span>
												<span>{{ origin_video_file.name }}</span>
											</template>

											<template v-else>
												<i class="fa fa-cloud-upload" />
												<span>アップロード</span>
											</template>
										</template>
									</template>
								</div>
							</div>
						</vuedropzone>

						<div class="d-flex flex-row mt-3">
							<span>{{ textError }}</span>
						</div>

						<div class="d-flex flex-row mt-3">
							<b-button
								@click="handleListenPusher()"
								style="background-color: #4300FF; color: #FFFFFF;"
								:disabled="!current_channel_id || target_file_id || is_processing_mosaic_video"
								:class="[
									'nor-buton mr-3',
									!current_channel_id || target_file_id || is_processing_mosaic_video ? 'nor-buton-disabled' : ''
								]"
							>
								<i class="fas fa-rocket" />
								<span>処理開始</span>
							</b-button>

							<b-button
								@click="handleDeleteUploadedVideo()"
								:disabled="!current_channel_id || is_processing_mosaic_video"
								style="background-color: #C5172E; color: #FFFFFF;"
								:class="[
									'nor-buton',
									!current_channel_id || is_processing_mosaic_video ? 'nor-buton-disabled' : ''
								]"
							>
								<i class="fas fa-trash" />
								<span>ビデオを削除</span>
							</b-button>
						</div>

						<div v-if="target_file_id && mosaic_video_file.id" class="d-flex flex-row mt-3">
							<b-button
								class="nor-buton mr-3"
								style="background-color: #347433; color: #FFFFFF;"
								@click="handleDownloadMosaicVideo()"
							>
								<i class="fas fa-download" />
								<span>結果動画をダウンロードする</span>
							</b-button>
						</div>
					</div>
				</template>
			</b-modal>
		</div>
	</b-overlay>
</template>

<script>
import 'vue2-datepicker/index.css';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';

import { hasRole } from '@/utils/hasRole';
import { obj2Path } from '@/utils/obj2Path';
import { cleanObj } from '@/utils/handleObj';
import { MakeToast } from '@/utils/MakeToast';
import { getListDepartment, getListFile, deleteFile, changeOrder, getMoisacVideo } from '@/api/modules/driverRecorder';

import axios from 'axios';
import draggable from 'vuedraggable';
import CONST_ROLE from '@/const/role';
import DatePicker from 'vue2-datepicker';
import vue2Dropzone from 'vue2-dropzone';
import vButton from '@/components/atoms/vButton';
import vHeaderPage from '@/components/atoms/vHeaderPage';
import vPagination from '@/components/atoms/vPagination';
import FilterListDriverRecorder from '../components/FilterListDriverRecorder.vue';

const URL_API = {
    apiDeleteFile: '/driver-recorder',
    apiGetListFile: '/driver-recorder',
    apiCreatePlaylist: '/api/driver-play-list',
    apiDeletePlaylist: '/api/driver-play-list',
    apiDownloadFile: '/driver-recorder/download',
    apiGetListDepartment: '/department/list-all',
    apiChangeOrder: '/driver-play-list/update-position',
    apiGetPlaylist: '/api/driver-recorder-play-list-viewer',
    apiGetFileMosaicVideo: '/driver-recorder/video-deface',
    apiPostFileForMosaic: '/api/driver-recorder/upload-file-deface',
    apiCreateOrUpdatePlaylist: '/api/driver-recorder/add-or-update-play-list',
};

export default {
    name: 'DriverRecorderList',
    components: {
        vButton,
        draggable,
        DatePicker,
        vHeaderPage,
        vPagination,
        FilterListDriverRecorder,
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

            hasRole,

            filter: {
                accidentDate: {
                    status: false,
                    value: null,
                },
                department: {
                    status: false,
                    value: null,
                },
                title: {
                    status: false,
                    value: '',
                },
                type_one: {
                    status: false,
                    value: null,
                },
                type_two: {
                    status: false,
                    value: null,
                },
                shipper: {
                    status: false,
                    value: null,
                },
                accident_classification: {
                    status: false,
                    value: null,
                },
                place_of_occurrence: {
                    status: false,
                    value: null,
                },
            },

            listDepartment: [],

            listType: [
                { value: 0, text: this.$t('DRIVER_RECORDER_INDEX.ACCIDENT') },
                { value: 1, text: this.$t('DRIVER_RECORDER_INDEX.OTHER') },
            ],

            pickerYearMonth: this.$store.getters.yearMonthPicker || {
                month: null,
                year: null,
                textMonth: '',
                textYear: '',
                textFull: '',
            },

            items: [],

            filterQuery: {
                sort_by: null,
                sort_type: null,
            },

            pagination: {
                current_page: 1,
                per_page: 20,
                total_rows: 0,
            },

            showModal: false,

            tempID: '',

            role: this.$store.getters.profile.roles[0],

            hasAccess: [
                CONST_ROLE.CREW,
                CONST_ROLE.CLERKS,
                CONST_ROLE.TL,
                CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                CONST_ROLE.PERSONNEL_LABOR,
                CONST_ROLE.GENERAL_AFFAIRS,
                CONST_ROLE.ACCOUNTING,
                CONST_ROLE.AM_SM,
                CONST_ROLE.QUALITY_CONTROL,
                CONST_ROLE.SALES,
                CONST_ROLE.SITE_MANAGER,
                CONST_ROLE.HQ_MANAGER,
                CONST_ROLE.DEPARTMENT_MANAGER,
                CONST_ROLE.EXECUTIVE_OFFICER,
                CONST_ROLE.DIRECTOR,
                CONST_ROLE.DX_USER,
                CONST_ROLE.DX_MANAGER,
            ],

            hasAccessRegister: [
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

            hasAccessDetail: [
                CONST_ROLE.CREW,
                CONST_ROLE.CLERKS,
                CONST_ROLE.TL,
                CONST_ROLE.DEPARTMENT_OFFICE_STAFF,
                CONST_ROLE.PERSONNEL_LABOR,
                CONST_ROLE.GENERAL_AFFAIRS,
                CONST_ROLE.ACCOUNTING,
                CONST_ROLE.AM_SM,
                CONST_ROLE.QUALITY_CONTROL,
                CONST_ROLE.SALES,
                CONST_ROLE.SITE_MANAGER,
                CONST_ROLE.HQ_MANAGER,
                CONST_ROLE.DEPARTMENT_MANAGER,
                CONST_ROLE.EXECUTIVE_OFFICER,
                CONST_ROLE.DIRECTOR,
                CONST_ROLE.DX_USER,
                CONST_ROLE.DX_MANAGER,
            ],

            hasAccessDelete: [
                CONST_ROLE.QUALITY_CONTROL,
                CONST_ROLE.SITE_MANAGER,
                CONST_ROLE.HQ_MANAGER,
                CONST_ROLE.DEPARTMENT_MANAGER,
                CONST_ROLE.EXECUTIVE_OFFICER,
                CONST_ROLE.DIRECTOR,
                CONST_ROLE.DX_USER,
                CONST_ROLE.DX_MANAGER,
            ],

            haveAccessRegiserEditPlaylist: [
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

            isHasAccessRight: false,

            showModalPlaylistDetail: false,
            showModalPlaylistCreate: false,
            showModalPlaylistIndex: false,
            showModalDeleteConfirm: false,

            playlist_title: '',
            playlist_thumbnail: {
                url: '',
                name: '',
                id: null,
            },

            dropzoneImageOptions: {
                method: 'POST',
                headers: { 'Authorization': this.$store.getters.token },
                url: `${window.origin}/api/driver-recorder/upload-file`,

                parallelChunkUploads: false,
                chunking: true,
                chunkSize: 10000000,

                maxFiles: 1,
                maxFilesize: 3 * 1024,
                uploadMultiple: false,

                acceptedFiles: 'image/png, image/jpg, image/jpeg, application/pdf',

                previewTemplate: this.template(),
            },

            dropzoneVideoOptions: {
                method: 'POST',
                headers: { 'Authorization': this.$store.getters.token },
                url: `${window.origin}${URL_API.apiPostFileForMosaic}`,

                parallelChunkUploads: false,
                chunking: true,
                chunkSize: 10000000,

                maxFiles: 1,
                maxFilesize: 1 * 1024 * 1024 * 1024 * 1024,
                uploadMultiple: false,

                acceptedFiles: 'video/mp4, video/webm, video/quicktime, video/x-matroska',

                previewTemplate: this.template(),
            },

            playlist_items: [],

            deleting_playlist_index: null,

            current_recorder_id: null,

            current_playlist_index: null,

            list_playlist: [],

            list_new_order: [],

            drag: false,

            list_temp_driver_recorder: [],

            date: this.$store.getters.yearMonthPickerDriverRecorder['date'],
            year: this.$store.getters.yearMonthPickerDriverRecorder['year'],
            month: this.$store.getters.yearMonthPickerDriverRecorder['month'],

            year_month_picker: '',
            ja_locale: {
                months: [
                    '1月',
                    '2月',
                    '3月',
                    '4月',
                    '5月',
                    '6月',
                    '7月',
                    '8月',
                    '9月',
                    '10月',
                    '11月',
                    '12月',
                ],
            },

            min_year: 2020,
            max_year: 2070,

            textError: '',
            current_channel_id: null,
            showModalMosaicVideo: false,
            is_processing_mosaic_video: false,
            processing_mosaic_video_percentage: 0,

            origin_video_file: {
                url: '',
                name: '',
                id: null,
            },

            target_file_id: null,
            is_uploading_origin_video: false,

            mosaic_video_file: {
                url: '',
                name: '',
                id: null,
            },
        };
    },
    computed: {
        headerTable() {
            return [
                { key: 'record_date', sortable: true, label: this.$t('DRIVER_RECORDER.TABLE_ACCIDENT_DATE'), thClass: 'th-accident-date' },
                { key: 'department_id', sortable: true, label: this.$t('DRIVER_RECORDER.TABLE_DEPARTMENT'), thClass: 'th-department' },
                { key: 'type_one', sortable: true, label: '事故GP', thClass: 'th-type' },
                { key: 'type_two', sortable: true, label: '有責無責', thClass: 'th-type' },
                { key: 'shipper', sortable: true, label: '荷主', thClass: 'th-type' },
                { key: 'accident_classification', sortable: true, label: '事故区分', thClass: 'th-type' },
                { key: 'place_of_occurrence', sortable: true, label: '発生場所', thClass: 'th-type' },
                { key: 'title', sortable: false, label: this.$t('DRIVER_RECORDER.TALBE_ACCIDENT_TITLE'), thClass: 'th-accident-title' },
                { key: 'play', sortable: false, label: this.$t('DRIVER_RECORDER.TABLE_PLAY'), thClass: 'th-play' },
                { key: 'download', sortable: false, label: this.$t('DRIVER_RECORDER.TABLE_DOWNLOAD'), thClass: 'th-download' },

                this.haveAccessRegiserEditPlaylist.includes(this.role)
                    ? { key: 'playlist', sortable: false, label: 'プレイリスト', thClass: 'th-download' }
                    : {},

                this.hasAccessDetail.includes(this.role)
                    ? { key: 'detail', sortable: false, label: this.$t('DRIVER_RECORDER.TABLE_DETAIL'), thClass: 'th-detail' }
                    : {},

                this.hasAccessDelete.includes(this.role)
                    ? { key: 'delete', sortable: false, label: this.$t('DRIVER_RECORDER.TABLE_DELETE'), thClass: 'th-delete' }
                    : {},
            ];
        },
        optionsPerPage() {
            return [
                { value: 20, text: '20' },
                { value: 50, text: '50' },
                { value: 100, text: '100' },
                { value: 250, text: '250' },
                { value: 500, text: '500' },
            ];
        },
        perPageChange() {
            return this.pagination.per_page;
        },
        lang() {
            return this.$store.getters.language;
        },
        dateChange() {
            return this.$store.getters.yearMonthPickerDriverRecorder;
        },
        environment() {
            return process.env.MIX_APP_ENV;
        },
    },
    watch: {
        perPageChange() {
            const STORED_DATA = this.$store.getters.driverRecorderPerPage;

            if (this.pagination.per_page !== STORED_DATA.per_page) {
                this.handleGetListFile();

                this.pagination.current_page = 1;

                this.$store.dispatch('driver_recorder/setCurrentPage', 1);
                this.$store.dispatch('driver_recorder/setPerPage', this.pagination.per_page);
            }
        },
        dateChange() {
            this.date = this.$store.getters.yearMonthPickerDriverRecorder['date'];
            this.year = this.$store.getters.yearMonthPickerDriverRecorder['year'];
            this.month = this.$store.getters.yearMonthPickerDriverRecorder['month'];
        },
    },
    created() {
        this.initData();
    },
    destroyed() {
        this.destroyedEventBus();
    },
    methods: {
        dateMonth(year, month) {
            if (this.validateYear(year) === false) {
                return '';
            }

            if (this.validateMonth(month) === false) {
                return '';
            }

            return this.formatYearMonth(year, month);
        },
        validateYear(year) {
            const re = /^[1-9]\d{3,}$/;

            return re.test(year);
        },
        validateMonth(month) {
            if (month >= 1 && month <= 12) {
                return true;
            }

            return false;
        },
        formatYearMonth(year, month) {
            if (month >= 1 && month <= 9) {
                month = '0' + month;
            }

            return `${year}-${month}`;
        },
        async minus() {
            this.month = parseInt(this.month);
            this.year = parseInt(this.year);
            if (this.month > 1) {
                this.month = this.month - 1;
            } else if (this.month === 1) {
                this.month = 12;
                this.year = this.year - 1;
            }
            this.emitData();
        },
        async plus() {
            this.month = parseInt(this.month);
            this.year = parseInt(this.year);
            if (this.month < 12) {
                this.month = this.month + 1;
            } else if (this.month === 12) {
                this.month = 1;
                this.year = this.year + 1;
            }
            this.emitData();
        },
        async emitData() {
            const DATA = {
                date: `${this.year}-${this.handleFormatMonth(this.month)}`,
                year: this.year,
                month: this.handleFormatMonth(this.month),
            };

            await this.$store.dispatch('driverRecorder/setDriverRecorderYearMonth', DATA);

            this.handleGetListFile();
        },
        disabledDate(date) {
            return date.getFullYear() < this.min_year || date.getFullYear() > this.max_year;
        },
        async handleChangeInput(event) {
            if (event) {
                const DATA = {
                    date: event,
                    year: event.split('-')[0],
                    month: event.split('-')[1],
                };
                await this.$store.dispatch('driverRecorder/setDriverRecorderYearMonth', DATA);
                this.handleGetListFile();
            }
        },
        setListTempDriverRecorder(index) {
            this.list_temp_driver_recorder = [];
            this.list_temp_driver_recorder = [...this.playlist_items[index]['driver_recorder']];
        },
        dragChanged(index) {
            this.list_new_order = [];

            const newListOne = [...this.list_temp_driver_recorder].map((item, index) => {
                const newSort = index;

                item.hasChanged = item.sortOrder !== newSort;

                if (item.hasChanged) {
                    item.sortOrder = newSort;
                }

                return item;
            });

            this.playlist_items[index]['driver_recorder'] = [...newListOne];

            const newListTwo = [...this.list_temp_driver_recorder].map((item, index) => {
                const newSort = index;

                item.hasChanged = item.sortOrder !== newSort;

                if (item.hasChanged) {
                    item.sortOrder = newSort;
                }

                return item['pivot'];
            });

            this.list_new_order = [...newListTwo];

            this.handleChangeOrder(this.list_new_order);
        },
        async handleChangeOrder(data = []) {
            try {
                const URL = `${URL_API.apiChangeOrder}`;

                const DATA = {
                    'list_position': data,
                };

                await changeOrder(URL, DATA);
            } catch (error) {
                console.log(error);
            }
        },
        template() {
            return `<div></div>`;
        },
        createdEventBus() {
            this.$bus.on('DRIVER_RECORDER_FILTER_DATA', filter => {
                this.filter = filter;
            });

            this.$bus.on('DRIVER_RECORDER_FILTER_APPLY', () => {
                console.log('DRIVER_RECORDER_FILTER_APPLY');
                this.handleGetListFile();
            });
        },
        destroyedEventBus() {
            this.$bus.off('DRIVER_RECORDER_FILTER_DATA');
            this.$bus.off('DRIVER_RECORDER_FILTER_APPLY');
        },
        async initData() {
            try {
                this.overlay.show = true;

                await this.createdEventBus();

                await this.handleGetListFile();

                await this.handleGetListDepartment();

                this.overlay.show = false;
            } catch (err) {
                console.log('INIT_DATA_ERROR');
                console.log(err);
            }
        },
        async handleGetListFile() {
            this.overlay.show = true;

            const month = this.$store.getters.yearMonthPickerDriverRecorder;

            try {
                let PARAMS = {
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    department_id: this.filter.department.value,
                    record_date: this.filter.accidentDate.value,
                    title: this.filter.title.value,
                    type_one: this.filter.type_one.value,
                    type_two: this.filter.type_two.value,
                    shipper: this.filter.shipper.value,
                    accident_classification: this.filter.accident_classification.value,
                    place_of_occurrence: this.filter.place_of_occurrence.value,
                    month: month['date'],
                    sort_by: this.filterQuery.sort_by,
                    sort_type: this.filterQuery.sort_type,
                };

                PARAMS = cleanObj(PARAMS);

                const URL = `${URL_API.apiGetListFile}?${obj2Path(PARAMS)}`;

                const response = await getListFile(URL, PARAMS);

                if (response) {
                    this.items = response.result;

                    this.pagination.total_rows = response.pagination.total_records;
                } else {
                    this.items = [];
                }
            } catch (err) {
                this.items = [];
                console.log(err);
            }

            this.overlay.show = false;
        },
        async handleGetListDepartment() {
            try {
                const { code, data } = await getListDepartment(URL_API.apiGetListDepartment);

                if (code === 200) {
                    this.listDepartment = data;
                } else {
                    this.listDepartment = [];
                }
            } catch (err) {
                this.listDepartment = [];
                console.log(err);
            }
        },
        handleSort(ctx) {
            this.filterQuery.sort_by = ctx.sortBy === 'record_date' ? 'record_date' : ctx.sortBy;
            this.filterQuery.sort_by = ctx.sortBy === 'type_one' ? 'type_one' : ctx.sortBy;
            this.filterQuery.sort_by = ctx.sortBy === 'type_two' ? 'type_two' : ctx.sortBy;
            this.filterQuery.sort_by = ctx.sortBy === 'shipper' ? 'shipper' : ctx.sortBy;
            this.filterQuery.sort_by = ctx.sortBy === 'accident_classification' ? 'accident_classification' : ctx.sortBy;
            this.filterQuery.sort_by = ctx.sortBy === 'place_of_occurrence' ? 'place_of_occurrence' : ctx.sortBy;

            if (ctx.sortBy === 'department_id') {
                if (ctx.sortDesc) {
                    this.filterQuery.sort_type = 'desc';
                } else {
                    this.filterQuery.sort_type = 'asc';
                }
            } else {
                this.filterQuery.sort_type = ctx.sortDesc ? 'asc' : 'desc';
            }

            this.handleGetListFile();
        },
        getCurrentPage(value) {
            if (value) {
                const STORED_DATA = this.$store.getters.driverRecorderCurrentPage;

                if (value !== STORED_DATA.current_page) {
                    this.pagination.current_page = value;
                    this.$store.dispatch('driver_recorder/setCurrentPage', this.pagination.current_page);
                    this.handleGetListFile();
                }
            }
        },
        onClickUpload() {
            this.$router.push({ name: 'DriverRecorderCreate' });
        },
        onClickDetail(id) {
            this.$router.push({ name: 'DriverRecorderDetail', params: { id }});
        },
        convertType(type, index) {
            if (index === 1) {
                switch (type) {
                case 1:
                    return '事故';
                case 2:
                    return 'GP';
                case 3:
                    return 'その他';
                default:
                    break;
                }
            } else if (index === 2) {
                switch (type) {
                case 1:
                    return '有責';
                case 2:
                    return '無責';
                case 3:
                    return 'その他';
                default:
                    break;
                }
            } else if (index === 3) {
                switch (type) {
                case 1:
                    return '山崎製パン';
                case 2:
                    return 'ヤマ物';
                case 3:
                    return 'サンロジ';
                case 4:
                    return '富士エコー';
                case 5:
                    return 'パスコ';
                case 6:
                    return 'ロジネット';
                case 7:
                    return 'FR';
                case 8:
                    return 'その他';
                default:
                    break;
                }
            } else if (index === 4) {
                switch (type) {
                case 1:
                    return '接触(物)';
                case 2:
                    return '接触(車)';
                case 3:
                    return '接触(人)';
                case 4:
                    return '追突';
                case 5:
                    return 'バック';
                case 6:
                    return '自損横転';
                case 7:
                    return 'オーバーハング';
                case 8:
                    return '巻込み';
                case 9:
                    return '衝突';
                case 10:
                    return '不明・その他';
                default:
                    break;
                }
            } else if (index === 5) {
                switch (type) {
                case 1:
                    return '店舗敷地';
                case 2:
                    return '構内';
                case 3:
                    return '一般道路';
                case 4:
                    return '交差点';
                case 5:
                    return '高速道路';
                case 6:
                    return '納品口';
                case 7:
                    return '駐車場';
                case 8:
                    return '不明・その他';
                default:
                    break;
                }
            }
        },
        async handleDeleteFile() {
            this.overlay.show = true;

            this.showModal = false;

            try {
                const URL = `${URL_API.apiDeleteFile}/${this.tempID}`;

                const response = await deleteFile(URL);

                if (response.code === 200) {
                    this.handleGetListFile();

                    MakeToast({
                        variant: 'success',
                        title: this.$t('SUCCESS'),
                        content: '削除しました',
                    });
                }
            } catch (error) {
                console.log(error);
            }

            this.overlay.show = false;
        },
        async handlePlayRecorder(id, idx) {
            if (id) {
                const route = this.$router.resolve({ name: 'PlayRecorder', params: { id, idx }});

                window.open(route.href);
            }
        },
        async onClickDownload(id) {
            try {
                const URL = `${window.origin}/api/driver-recorder/download/${id}`;

                window.location.href = URL;
            } catch (error) {
                console.log(error);
            }

            this.overlay.show = false;
        },
        handleFormatMonth(_month) {
            return _month < 10 ? `0${_month}` : _month;
        },
        handleValidatePermission() {
            if (this.hasAccess.includes(this.role)) {
                this.isHasAccessRight = true;
            } else {
                this.isHasAccessRight = false;
            }
        },
        async handleShowModalPlaylistIndex(id) {
            this.current_recorder_id = id;
            await this.handleGetPlaylist();
            this.showModalPlaylistIndex = true;
        },
        handleChangePlaylistStatus(id, index) {
            this.current_playlist_index = index;

            const TEMP_ARRAY = [...this.playlist_items];
            TEMP_ARRAY[index]['status'] = !TEMP_ARRAY[index]['status'];

            this.playlist_items = [...TEMP_ARRAY];

            this.items.forEach(element => {
                if (element['id'] === this.current_recorder_id) {
                    if (element['play_list'].length === 0) {
                        element['play_list'].push(id);
                    } else {
                        const isExist = element['play_list'].findIndex((item) => item === id);

                        if (isExist !== -1) {
                            element['play_list'].splice(isExist, 1);
                        } else {
                            element['play_list'].push(id);
                        }
                    }
                }
            });
        },
        handleShowModalPlaylistCreate() {
            this.showModalPlaylistIndex = false;
            this.showModalPlaylistCreate = true;
        },
        async handleSavePlaylistIndex() {
            try {
                const URL = `${URL_API.apiCreateOrUpdatePlaylist}/${this.current_recorder_id}`;

                const LIST_PLAY_LIST = this.items.find((item) => {
                    if (item['id'] === this.current_recorder_id) {
                        return item;
                    }
                });

                const DATA = {
                    list_play_list: LIST_PLAY_LIST['play_list'],
                };

                const response = await axios.post(URL, DATA, {
                    headers: { 'Authorization': this.$store.getters.token },
                });

                const STATUS = response.status;

                if (STATUS === 200) {
                    MakeToast({
                        variant: 'success',
                        title: this.$t('SUCCESS'),
                        content: 'プレイリストの更新に成功しました。',
                    });

                    this.showModalPlaylistIndex = false;
                } else {
                    console.log('API handleSavePlaylistIndex has failed.');
                }
            } catch (error) {
                console.log(error);
                this.showModalPlaylistIndex = false;
            }
        },
        afterAdded(file, type) {
            const reader = new FileReader();

            let max_file_size;

            if (type && type === 'video') {
                max_file_size = 1 * 1024 * 1024 * 1024 * 1024;
            } else {
                max_file_size = 3 * 1024 * 1024 * 1024;
            }

            if (file.size > max_file_size) {
                return;
            } else {
                reader.onload = () => {
                    if (type && type === 'video') {
                        this.origin_video_file.name = file?.name;
                    } else {
                        this.playlist_thumbnail.name = file?.name;
                    }
                };

                reader.readAsDataURL(file);
            }
        },
        afterComplete(response, type) {
            this.current_channel_id = null;

            const max_file_size = type === 'video' ? 1 * 1024 * 1024 * 1024 * 1024 : 3 * 1024 * 1024;

            if (response.size > max_file_size) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: type === 'video' ? 'Max file size is 1 GB' : 'Max file size is 3 MB',
                });
                return;
            }

            if (response.status === 'error') {
                this.resetFileState(type);
                return;
            }

            try {
                const DATA = JSON.parse(response.xhr.response);

                if (type === 'video' && DATA.channel) {
                    this.origin_video_file = {
                        id: DATA.id,
                        url: DATA.file_url,
                        name: response.name,
                    };

                    this.current_channel_id = DATA.channel;
                } else {
                    this.playlist_thumbnail = {
                        id: DATA.id,
                        url: DATA.file_url,
                    };
                }

                this.is_uploading_origin_video = false;
            } catch (e) {
                this.textError = 'アップロード応答の解析に失敗しました (Failed to parse upload response).';
                this.resetFileState(type);
            }
        },
        resetFileState(type) {
            if (type === 'video') {
                this.current_channel_id = null;
                this.origin_video_file = { id: null, url: '', name: '' };
            } else {
                this.playlist_thumbnail = { id: null, url: '' };
            }
        },
        handleRemoveFile() {
            this.playlist_thumbnail.id = null;
            this.playlist_thumbnail.name = '';
            this.playlist_thumbnail.url = '';
        },
        async handleCreatePlayList() {
            if (this.playlist_title === '') {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: 'プレイリスト名は必須です',
                });
            } else {
                try {
                    const DATA = {
                        name: this.playlist_title,
                        file_id: this.playlist_thumbnail.id,
                    };

                    const response = await axios.post(URL_API.apiCreatePlaylist, DATA, {
                        headers: {
                            'Authorization': this.$store.getters.token,
                        },
                    });

                    if (response.status === 200) {
                        MakeToast({
                            variant: 'success',
                            title: this.$t('SUCCESS'),
                            content: 'プレイリストが正常に作成されました',
                        });

                        this.playlist_title = '';

                        this.playlist_thumbnail.id = null;
                        this.playlist_thumbnail.name = '';
                        this.playlist_thumbnail.url = '';

                        this.showModalPlaylistCreate = false;
                    }
                } catch (error) {
                    console.log(error);
                }
            }
        },
        async handleShowModalPlaylistDetail() {
            await this.handleGetPlaylist();
            this.showModalPlaylistDetail = true;
        },
        async handleGetPlaylist() {
            try {
                this.list_playlist = [];
                this.playlist_items = [];

                const URL = URL_API.apiGetPlaylist;

                const response = await axios.get(URL);

                const STATUS = response.status;
                const DATA = response.data.data;

                if (STATUS === 200) {
                    this.playlist_items = DATA;

                    for (let i = 0; i < this.playlist_items.length; i++) {
                        for (let j = 0; j < this.items.length; j++) {
                            if (this.items[j]['id'] === this.current_recorder_id) {
                                if (this.items[j]['play_list'].includes(this.playlist_items[i]['id'])) {
                                    this.playlist_items[i]['status'] = true;
                                    this.playlist_items[i]['included_recorders'] = this.items[j]['play_list'];
                                } else {
                                    this.playlist_items[i]['status'] = false;
                                    this.playlist_items[i]['included_recorders'] = [];
                                }
                            }
                        }
                    }
                } else {
                    MakeToast({
                        variant: 'warning',
                        title: this.$t('WARNING'),
                        content: 'API handleGetPlaylist has failed.',
                    });
                }
            } catch (error) {
                console.log(error);
            }
        },
        handleOpenModalDeletePlaylist(index) {
            this.deleting_playlist_index = index;
            this.showModalPlaylistDetail = false;
            this.showModalDeleteConfirm = true;
        },
        handleCloseModalDeleteConfirmation() {
            this.deleting_playlist_index = null;
            this.showModalPlaylistDetail = true;
        },
        async handleDeletePlaylist() {
            try {
                if (this.deleting_playlist_index !== null) {
                    const URL = `${URL_API.apiDeletePlaylist}/${this.playlist_items[this.deleting_playlist_index]['id']}`;

                    const response = await axios.delete(URL, {
                        headers: {
                            'Authorization': this.$store.getters.token,
                        },
                    });

                    const STATUS = response.status;

                    if (STATUS === 200) {
                        MakeToast({
                            variant: 'success',
                            title: this.$t('SUCCESS'),
                            content: 'プレイリストの削除に成功しました',
                        });

                        this.playlist_items = [];
                        await this.handleGetPlaylist();

                        this.showModalDeleteConfirm = false;

                        this.showModalPlaylistDetail = true;
                    }
                } else {
                    console.log('[ERROR] - Undefined playlist index');
                }
            } catch (error) {
                console.log(error);

                this.showModalDeleteConfirm = false;
            }
        },
        handleNavigateToPlaylistScreen(id) {
            const URL = `${window.origin}/playlist/${id}`;
            window.open(URL, '_blank');
        },
        handleRenderClass(option) {
            if (option === 1) {
                return 'text-red';
            } else {
                return '';
            }
        },
        handleTurnOnModalMosaicVideo() {
            this.showModalMosaicVideo = true;
        },
        handleTurnOffModalMosaicVideo() {
            this.showModalMosaicVideo = false;
        },
        handleDeleteUploadedVideo() {
            this.origin_video_file.id = null;
            this.origin_video_file.name = '';
            this.origin_video_file.url = '';

            this.mosaic_video_file.id = null;
            this.mosaic_video_file.name = '';
            this.mosaic_video_file.url = '';

            this.target_file_id = null;
            this.current_channel_id = null;

            this.$refs.myVueDropzoneVideo.removeAllFiles(true);
        },
        handleListenPusher() {
            this.target_file_id = null;

            this.processing_mosaic_video_percentage = 0;

            this.mosaic_video_file = {
                url: '',
                name: '',
                id: null,
                preview_url: '',
            };

            this.$nextTick(() => {
                this.listenToChannel();
            });
        },
        listenToChannel() {
            this.is_processing_mosaic_video = true;

            window.Echo.channel(this.current_channel_id).listen('.cloud_deface_process_event', async(message) => {
                if (message) {
                    const percentage = message.process.percent;

                    this.processing_mosaic_video_percentage = percentage;

                    if (percentage >= 100) {
                        this.is_processing_mosaic_video = false;
                        this.target_file_id = message.process.deface_video.file_id;

                        if (this.target_file_id) {
                            await this.handleGetMoisacVideo();
                        }
                    }
                }
            });
        },
        async handleGetMoisacVideo() {
            if (this.target_file_id) {
                try {
                    const URL = `${URL_API.apiGetFileMosaicVideo}/${this.target_file_id}`;

                    const response = await getMoisacVideo(URL);

                    const { code, data } = response;

                    if (code === 200) {
                        this.mosaic_video_file = {
                            id: data.deface_video?.deface_file?.id || null,
                            url: data.deface_video?.deface_file?.file_url || '',
                            name: data.deface_video?.deface_file?.file_name || '',
                            preview_url: data.deface_video?.deface_file?.url_view_file || '',
                        };
                    } else {
                        console.warn('Failed to retrieve mosaic video:', response);

                        this.mosaic_video_file = {
                            id: null,
                            url: '',
                            name: '',
                            preview_url: '',
                        };
                    }
                } catch (error) {
                    console.error('Error fetching mosaic video:', error);
                }
            } else {
                console.warn('No target file ID available for mosaic video retrieval.');
            }
        },
        async handleDownloadMosaicVideo() {
            const file = this.mosaic_video_file;
            if (!file?.url) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: this.$t('モザイク動画がありません。'),
                });
                return;
            }

            try {
                const resp = await fetch(file.url, { mode: 'cors' });
                if (!resp.ok) {
                    throw new Error(`HTTP ${resp.status}`);
                }
                const blob = await resp.blob();

                const blobUrl = URL.createObjectURL(blob);

                const link = document.createElement('a');
                link.href = blobUrl;
                link.download = file.name ? `DEFACED_${file.name}` : 'DEFACED_NO_NAME.mp4';
                document.body.appendChild(link);
                link.click();

                link.remove();
                URL.revokeObjectURL(blobUrl);
            } catch (err) {
                console.error('Download error:', err);
                MakeToast({
                    variant: 'danger',
                    title: this.$t('DANGER'),
                    content: this.$t('ダウンロード中にエラーが発生しました。'),
                });
            }
        },
        onSending(file, xhr, formData) {
            this.is_uploading_origin_video = true;
        },
        onQueueComplete() {
            this.is_uploading_origin_video = false;
        },
        onError(file, errorMessage, xhr) {
            this.is_uploading_origin_video = false;
        },
    },
};
</script>

<style lang="scss">
.modal-create-playlist-header {
    color: #FFFFFF !important;
    background-color: #343a40 !important;
}

.modal-show-playlist-header {
    color: #FFFFFF !important;
    background-color: #343a40 !important;
}

.modal-detail-playlist-header {
    color: #FFFFFF !important;
    background-color: #343a40 !important;
}
</style>

<style lang="scss" scoped>
@import '@/scss/variables';

::v-deep .mx-datepicker {
    max-width: 42px !important;
}

::v-deep .mx-input {
    margin-left: 5px;
    border-radius: 5px;
    height: 39px !important;
    background-color: #0F0448;
}

::v-deep .mx-icon-calendar {
    color: #FFFFFF !important;
}

.date-selector {
    button {
        background-color: #0F0448;

        &:active {
            background-color: #0F0448;
        }

        &:focus {
            background-color: #0F0448;
        }
    }

    button.date {
        cursor: default;
        min-width: 90px;
        font-weight: 600;
        border-right: 1px solid gainsboro !important;
        border-left: 1px solid gainsboro !important;
        padding: 0 4px;
    }

    button.minus-btn,
    button.plus-btn {
        &:hover {
            opacity: .8 !important;
            background-color: #0F0448;
        }
    }

    button.plus-btn {
        border-top-right-radius: 6px !important;
        border-bottom-right-radius: 6px !important;
        border-left: 1px solid gainsboro !important;
    }

    button.minus-btn {
        border-top-left-radius: 6px !important;
        border-bottom-left-radius: 6px !important;
        border-right: 1px solid gainsboro !important;
    }
}

.text-red {
    color: red;
}

.handle:hover {
    cursor: pointer;
}

.confirm-delete-button {
    width: 120px;
    height: 40px;
    background-color: #ff8a20;

    &:hover {
        opacity: .6;
    }
}

.cancel-delete-button {
    width: 120px;
    height: 40px;

    &:hover {
        opacity: .6;
    }
}

.modal-header-card {
    width: 100%;
    display: flex;
    font-size: 20px;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;

    &>i:hover {
        opacity: .8;
        cursor: pointer;
    }
}

.playlist-img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 50%;
    animation: rotation 10s infinite linear;
    box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;

    &:hover {
        opacity: .8;
        cursor: pointer;
    }

    ;
}

.image-card {
    width: auto;
    height: 300px;
    object-fit: contain;
}

.card-holder {
    label {
        font-size: 20px;
        font-weight: bold;
    }

    .remove-file {
        color: #007bff;
    }

    .remove-file:hover {
        opacity: .8;
        cursor: pointer;
        text-decoration: underline;
    }
}

.upload-img-card {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 50px;
    border: 2px dashed #dee2e6;

    &>span {
        color: #000000;
    }

    &:hover {
        cursor: pointer;
        opacity: .4;
    }
}

.info-card {
    width: 100%;
    display: flex;
    align-items: center;
    flex-direction: row;
    justify-content: space-between;
    border-top: 1px dashed #dddddd;
    border-bottom: 1px dashed #dddddd;

    &>span {
        font-size: 16px;
        font-weight: bold;
    }

    .circle {
        width: 30px;
        height: 30px;
        display: flex;
        position: relative;
        border-radius: 5px;
        align-items: center;
        justify-content: center;
        border: 1px solid #dddddd;
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;

        .fa-check {
            color: #FFFFFF;
        }

        &:hover {
            opacity: .8;
            cursor: pointer;
        }
    }
}

.gray-bg-button {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000000;
    background-color: #f2f2f2;
    border-radius: 5px;

    &:hover {
        opacity: .6;
        cursor: pointer;
    }
}

::v-deep .fixed-group-text {
    min-width: 150px;
    display: flex;
    justify-content: center;
}

::-webkit-scrollbar {
    height: 3px;
    width: 3px;
}

::-webkit-scrollbar-thumb {
    border-radius: 45px;
}

::v-deep .btn-md {
    height: 40px;
    min-width: 120px;
}

.first {
    border-top: 1px dashed #ccc;
}

.item-play {
    width: 250px;
    cursor: pointer;
    padding-top: 6px;
    text-align: center;
    padding-bottom: 5px;
    border-bottom: 1px dashed #ccc;
}

.item-play:hover {
    color: #FFFFFF;
    background-color: #0f04486e;
}

.item-play-text {
    margin: 0 auto;
    display: table;
    font-size: 10px;
}

.driver-recorder-list {
    overflow: hidden;
    min-height: calc(100vh - 89px);

    .text-loading {
        margin-top: 10px;
    }

    &__title-header {
        margin-bottom: 20px;
    }

    &__filter,
    &__select-year-month,
    &__table-data {
        margin-bottom: 10px;
    }

    &__table-data {
        overflow: auto;
        min-height: calc(100vh - 370px);

        ::v-deep table#table-driver-recorder {
            thead {
                tr {
                    th {
                        background-color: $tolopea;
                        color: $white;

                        text-align: center;
                        vertical-align: middle;
                    }

                    .th-play,
                    .th-download,
                    .th-detail,
                    .th-delete {
                        width: 130px;
                    }

                    position: sticky;
                    top: 0;
                }
            }

            tbody {
                tr {
                    td {
                        text-align: center;
                        vertical-align: middle;

                        i {
                            cursor: pointer;
                        }
                    }

                    &:hover {
                        td {
                            background-color: $west-side;
                            color: $white;
                        }
                    }
                }
            }
        }
    }

    &__pagination {
        .select-per-page {
            #per-page {
                width: 100px;
            }
        }

        .show-pagination {
            display: flex;
            justify-content: center;
        }
    }
}

::v-deep .item-play {
    margin: 5px 0;
}

::v-deep #modal-cf {
    .modal-custom-header {
        border-bottom: 0 none;
    }

    .modal-custom-body {
        text-align: center;
        padding-top: 60px;

        span {
            font-weight: 500;
        }
    }

    .modal-custom-footer {
        border-top: 0 none;
        justify-content: center;
        padding-top: 50px;

        button {
            border: none;
            min-width: 150px;
            font-weight: 500;
            margin: 0 15px;

            &:hover {
                opacity: 0.8;
            }

            &:focus {
                opacity: 0.8;
            }
        }

        .modal-btn {
            background-color: $west-side;
            color: $white;

            &:focus {
                background-color: $west-side;
                color: $white;
            }
        }
    }
}

@keyframes rotation {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(359deg);
    }
}

.dropzone-video-custom-content {
    width: 100%;
    margin: 0 auto;
    color: #333333;
    cursor: pointer;
    font-weight: 600;
    user-select: none;
    padding: 10px 60px;
    text-align: center;
    border: 1px dashed #DDDDDD;
}

.dropzone-video-custom-content:hover {
    opacity: .6;
}

.nor-buton {
    cursor: pointer;
    min-width: 150px;
    font-weight: bold;

    &:hover {
        opacity: .6;
    }
}

.nor-buton-disabled {
    cursor: not-allowed;
    background-color: #33333380 !important;
}

::v-deep .dz-max-files-reached {
    cursor: default;
    pointer-events: none;
}
</style>
