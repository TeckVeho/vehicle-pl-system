<template>
	<div class="component-upload-file">
		<div v-if="isEdit === true" class="zone-add">
			<b-button class="btn-add" @click="onClickAdd()">
				<i class="far fa-plus-circle" />
				<span>{{ $t('COMPONENT_UPLOAD_FILE.BUTTON_ADD') }}</span>
			</b-button>
		</div>

		<template v-if="isEmpty === true">
			<b-card>
				<div class="text-center">{{ $t('TABLE_EMPTY') }}</div>
			</b-card>
		</template>

		<template v-if="isEmpty === false">
			<template v-if="isEdit === true">
				<b-card>
					<div class="zone-list-upload-file">
						<div v-for="(table, idx) in sampleUploadData" :key="idx" class="table-upload-file">
							<div class="title-movie">
								<div class="d-flex flex-row">
									<div class="zone-delete w-100">
										<label>{{ $t('COMPONENT_UPLOAD_FILE.MOVIE_TITLE') }}</label>

										<b-button v-if="idx > 0" class="float-right" :disabled="table.hasProcessRunning" @click="onClickDelete(idx, table.id)">
											<i class="fas fa-trash" />
											<span>{{ $t('COMPONENT_UPLOAD_FILE.BUTTON_DELETE') }}</span>
										</b-button>
									</div>
								</div>
								<b-form-input v-model="table.title" placeholder="入力してください" @input.native="keydownHandler($event, table.id, table.title)" />
							</div>

							<b-table-simple class="upload-file" bordered>
								<b-thead>
									<b-tr>
										<b-th class="th-type">
											{{ $t('COMPONENT_UPLOAD_FILE.TYPE') }}
										</b-th>
										<b-th class="th-file">
											{{ $t('COMPONENT_UPLOAD_FILE.FILE') }}
										</b-th>
									</b-tr>
								</b-thead>

								<b-tbody>
									<b-tr>
										<b-td class="td-type">
											<span>{{ $t('COMPONENT_UPLOAD_FILE.FRONT') }}</span>
										</b-td>

										<b-td class="td-file">
											<div v-show="table.list_movie.front.isCompleted === false" class="upload-zone">
												<b-row class="upload-zone-row">
													<b-col cols="12" class="upload-zone-col-left">
														<vueDropzone
															id="customdropzone"
															:options="dropzoneOptions"
															:use-custom-slot="true"
															:include-styling="false"
															@vdropzone-complete="afterComplete($event, table.id, 'front')"
															@vdropzone-file-added="afterAdded($event, table.id, 'front')"
															@vdropzone-upload-progress="onUploadProgress($event, table.id, 'front')"
														>
															<div class="dropzone-custom-content" style="margin: 5px;">
																<div class="d-row flex-row text-center">
																	<i class="fa fa-cloud-upload" />
																	<span>アップロード</span>
																</div>
															</div>
														</vueDropzone>
													</b-col>
												</b-row>
											</div>

											<div v-if="table.list_movie.front.progressPercentage === 100" class="dropzone-custom-content extra" style="margin-top: 5px;">
												<div :class="['w-100 text-center completed-bar', status]">
													<div class="d-flex flex-row w-100 justify-content-between pl-2 pr-2" style="position: relative;">
														<p class="completed-bar-file-name" style="padding: 0px !important;">{{ `${table.list_movie.front.fileName}` }}</p>
														<i class="far fa-times-circle completed-bar-icon" style="position: absolute; right: -30px;" @click="handleRemoveVideo(table.id, 'front', table.list_movie.front.id)" />
													</div>
												</div>
											</div>

											<b-progress
												v-if="table.list_movie.front.progressPercentage > 0 && table.list_movie.front.progressPercentage < 100"
												:max="100"
												height="2rem"
												:striped="false"
												progress="true"
												style="margin-top: 5px;"
											>
												<b-progress-bar :value="table.list_movie.front.progressPercentage" variant="success">
													<span style="font-size: 12px; color: #000000;">
														{{ `${transformPercentage(table.list_movie.front.progressPercentage)} % - ${transformBandwidth(table.list_movie.front.progressBandwidth)} MB` }}
													</span>
												</b-progress-bar>
											</b-progress>
										</b-td>
									</b-tr>

									<b-tr>
										<b-td class="td-type">
											{{ $t('COMPONENT_UPLOAD_FILE.INSIDE') }}
										</b-td>

										<b-td class="td-file">
											<div v-show="table.list_movie.inside.isCompleted === false" class="upload-zone">
												<b-row class="upload-zone-row">
													<b-col cols="12" class="upload-zone-col-left">
														<vueDropzone
															id="customdropzone"
															:options="dropzoneOptions"
															:use-custom-slot="true"
															:include-styling="false"
															@vdropzone-complete="afterComplete($event, table.id, 'inside')"
															@vdropzone-file-added="afterAdded($event, table.id, 'inside')"
															@vdropzone-upload-progress="onUploadProgress($event, table.id, 'inside')"
														>
															<div class="dropzone-custom-content" style="margin: 5px;">
																<div class="d-row flex-row text-center">
																	<i class="fa fa-cloud-upload" />
																	<span>アップロード</span>
																</div>
															</div>
														</vueDropzone>
													</b-col>
												</b-row>
											</div>

											<div v-if="table.list_movie.inside.progressPercentage === 100" class="dropzone-custom-content extra" style="margin-top: 5px;">
												<div :class="['w-100 text-center completed-bar', status]">
													<div class="d-flex flex-row w-100 justify-content-between pl-2 pr-2" style="position: relative;">
														<p class="completed-bar-file-name" style="padding: 0px !important;">{{ `${table.list_movie.inside.fileName}` }}</p>
														<i class="far fa-times-circle completed-bar-icon" style="position: absolute; right: -30px;" @click="handleRemoveVideo(table.id, 'inside', table.list_movie.inside.id)" />
													</div>
												</div>
											</div>

											<b-progress
												v-if="table.list_movie.inside.progressPercentage > 0 && table.list_movie.inside.progressPercentage < 100"
												:max="100"
												height="2rem"
												:striped="false"
												progress="true"
												style="margin-top: 5px;"
											>
												<b-progress-bar :value="table.list_movie.inside.progressPercentage" variant="success">
													<span style="font-size: 12px; color: #000000;">
														{{ `${transformPercentage(table.list_movie.inside.progressPercentage)} % - ${transformBandwidth(table.list_movie.inside.progressBandwidth)} MB` }}
													</span>
												</b-progress-bar>
											</b-progress>
										</b-td>
									</b-tr>

									<b-tr>
										<b-td class="td-type">
											{{ $t('COMPONENT_UPLOAD_FILE.BEHIND') }}
										</b-td>

										<b-td class="td-file">
											<div v-show="table.list_movie.behind.isCompleted === false" class="upload-zone">
												<b-row class="upload-zone-row">
													<b-col cols="12" class="upload-zone-col-left">
														<vueDropzone
															id="customdropzone"
															:options="dropzoneOptions"
															:use-custom-slot="true"
															:include-styling="false"
															@vdropzone-complete="afterComplete($event, table.id, 'behind')"
															@vdropzone-file-added="afterAdded($event, table.id, 'behind')"
															@vdropzone-upload-progress="onUploadProgress($event, table.id, 'behind')"
														>
															<div class="dropzone-custom-content" style="margin: 5px;">
																<div class="d-row flex-row text-center">
																	<i class="fa fa-cloud-upload" />
																	<span>アップロード</span>
																</div>
															</div>
														</vueDropzone>
													</b-col>
												</b-row>
											</div>

											<div v-if="table.list_movie.behind.progressPercentage === 100" class="dropzone-custom-content extra" style="margin-top: 5px;">
												<div :class="['w-100 text-center completed-bar', status]">
													<div class="d-flex flex-row w-100 justify-content-between pl-2 pr-2" style="position: relative;">
														<p class="completed-bar-file-name" style="padding: 0px !important;">{{ `${table.list_movie.behind.fileName}` }}</p>
														<i class="far fa-times-circle completed-bar-icon" style="position: absolute; right: -30px;" @click="handleRemoveVideo(table.id, 'behind', table.list_movie.behind.id)" />
													</div>
												</div>
											</div>

											<b-progress
												v-if="table.list_movie.behind.progressPercentage > 0 && table.list_movie.behind.progressPercentage < 100"
												:max="100"
												height="2rem"
												:striped="false"
												progress="true"
												style="margin-top: 5px;"
											>
												<b-progress-bar :value="table.list_movie.behind.progressPercentage" variant="success">
													<span style="font-size: 12px; color: #000000;">
														{{ `${transformPercentage(table.list_movie.behind.progressPercentage)} % - ${transformBandwidth(table.list_movie.behind.progressBandwidth)} MB` }}
													</span>
												</b-progress-bar>
											</b-progress>
										</b-td>
									</b-tr>
								</b-tbody>
							</b-table-simple>
						</div>
					</div>
				</b-card>
			</template>

			<template v-if="isEdit === false">
				<b-card>
					<div class="zone-view-list-upload-file">
						<div v-for="(table, idx) in sampleUploadData" :key="idx" class="table-view-upload-file">
							<div class="title-movie">
								<div class="zone-play">
									<b-button @click="onClickPlay(idx)">
										<i class="fas fa-play" />
										{{ $t('COMPONENT_UPLOAD_FILE.BUTTON_PLAY') }}
									</b-button>
								</div>

								<span>
									<label for="movie-title-input">{{ $t('COMPONENT_UPLOAD_FILE.MOVIE_TITLE') }}:</label>
									<b-form-input id="movie-title-input" v-model="table.movie_title" disabled />
								</span>
							</div>

							<b-table-simple class="upload-file" bordered>
								<b-thead>
									<b-tr>
										<b-th class="th-type">
											{{ $t('COMPONENT_UPLOAD_FILE.TYPE') }}
										</b-th>
										<b-th class="th-file">
											{{ $t('COMPONENT_UPLOAD_FILE.FILE') }}
										</b-th>
									</b-tr>
								</b-thead>

								<b-tbody>
									<b-tr>
										<b-td class="td-type">
											{{ $t('COMPONENT_UPLOAD_FILE.FRONT') }}
										</b-td>
										<b-td class="td-file">
											<span v-if="table.front">{{ table.front.file_name }}</span>
											<span v-else />
										</b-td>
									</b-tr>
									<b-tr>
										<b-td class="td-type">
											{{ $t('COMPONENT_UPLOAD_FILE.INSIDE') }}
										</b-td>
										<b-td class="td-file">
											<span v-if="table.inside">{{ table.inside.file_name }}</span>
											<span v-else />
										</b-td>
									</b-tr>
									<b-tr>
										<b-td class="td-type">
											{{ $t('COMPONENT_UPLOAD_FILE.BEHIND') }}
										</b-td>
										<b-td class="td-file">
											<span v-if="table.behind">{{ table.behind.file_name }}</span>
											<span v-else />
										</b-td>
									</b-tr>
								</b-tbody>
							</b-table-simple>
						</div>
					</div>
				</b-card>
			</template>
		</template>
	</div>
</template>

<script>
import vue2Dropzone from 'vue2-dropzone';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';

export default {
    name: 'UploadFile',
    components: {
        vueDropzone: vue2Dropzone,
    },
    props: {
        isEdit: {
            type: Boolean,
            required: true,
            default: false,
        },
        sampleUploadData: {
            type: Array,
            required: true,
            default: function() {
                return [];
            },
        },
    },
    data() {
        return {
            status: '',

            statusText: '',
        };
    },
    computed: {
        isEmpty() {
            return this.sampleUploadData.length === 0;
        },
        dropzoneOptions() {
            return {
                method: 'POST',

                headers: { 'Authorization': this.$store.getters.token },

                parallelChunkUploads: false,

                chunking: true,
                chunkSize: 5000000,

                // maxFiles: 1,
                maxFilesize: 50000,
                uploadMultiple: false,

                thumbnailWidth: 200,
                thumbnailHeight: 200,

                acceptedFiles: 'video/mp4,video/x-m4v,video/*',

                url: `${window.origin}/api/driver-recorder/upload-file`,

                addRemoveLinks: true,
                dictRemoveFile: 'ファイルを削除',

                dictDefaultMessage: `<i class='fa fa-cloud-upload' /> アップロード`,
                dictCancelUpload: `キャンセル`,
                dictCancelUploadConfirmation: `このアップロードをキャンセルしてもよろしいですか?`,
                dictInvalidFileType: `このタイプのファイルはアップロードできません。`,
                dictMaxFilesExceeded: 'ファイルのアップロードは最大1つです。',

                previewTemplate: this.template(),
            };
        },
        TEMP_DATA() {
            return JSON.parse(JSON.stringify(this.sampleUploadData));
        },
    },
    methods: {
        template() {
            return `<div></div>`;
        },
        onClickAdd() {
            this.$emit('add');
        },
        onClickDelete(idx = -1, id) {
            this.$emit('delete', idx);
            this.$bus.emit('DELETE_UPLOAD_BLOCK', id);
        },
        onClickPlay(index) {
            this.$bus.emit('ON_CLICK_PLAY', index);
        },
        handleRedirectClickEvent(table, idx) {
            this.modalShow = true;
        },
        async onUploadProgress(file, id, position) {
            const _progress = file.upload.progress;
            const _bytesSent = file.upload.bytesSent;

            const element = document.getElementsByClassName('dz-processing')[0];

            if (element) {
                element.remove();
            }

            if (_progress !== 100) {
                this.TEMP_DATA[id].hasProcessRunning = true;
                this.TEMP_DATA[id].list_movie[position].progressPercentage = _progress;
                this.TEMP_DATA[id].list_movie[position].progressBandwidth = _bytesSent;
            }

            this.$bus.emit('ACTION_CHANGE_UPLOAD_DATA', this.TEMP_DATA);

            this.$bus.emit('HAS_PROCESS_RUNNING', true);
        },
        afterAdded(file, id, position) {
            const element = document.getElementsByClassName('dz-processing')[0];

            if (element) {
                element.remove();
            }

            this.TEMP_DATA[id].hasProcessRunning = false;
            this.TEMP_DATA[id].list_movie[position].isCompleted = true;
            this.TEMP_DATA[id].list_movie[position].fileName = file.name;

            this.$bus.emit('ACTION_CHANGE_UPLOAD_DATA', this.TEMP_DATA);

            this.$bus.emit('HAS_PROCESS_RUNNING', false);
        },
        afterComplete(response, id, position) {
            if (response.xhr.status === 201) {
                const DATA = JSON.parse(response.xhr.response);

                this.TEMP_DATA[id].list_movie[position].isCompleted = true;
                this.TEMP_DATA[id].list_movie[position].progressPercentage = 100;
                this.TEMP_DATA[id].list_movie[position].id = DATA.id;

                this.status = 'status-success';
                this.statusText = 'DRIVER_RECORDER_REGISTER.COMPLETED';
            } else {
                this.TEMP_DATA[id].list_movie[position].progressBandwidth = 0;
                this.TEMP_DATA[id].list_movie[position].isCompleted = false;

                this.status = 'status-error';
                this.statusText = 'DRIVER_RECORDER_REGISTER.ERROR';
            }

            this.TEMP_DATA[id].hasProcessRunning = false;

            this.$bus.emit('ACTION_CHANGE_UPLOAD_DATA', this.TEMP_DATA);

            this.$bus.emit('HAS_PROCESS_RUNNING', false);

            this.$bus.emit('POST_DATA', this.TEMP_DATA);
        },
        handleRemoveVideo(id, position, position_id) {
            this.$bus.emit('REMOVE_POST_DATA', id, position);
        },
        keydownHandler(event, id, string) {
            if (string && id) {
                this.TEMP_DATA[id].movie_title = string;
            } else {
                this.TEMP_DATA[id].movie_title = '';
            }

            this.$bus.emit('POST_DATA', this.TEMP_DATA);
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
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables';

.dz-error {
  background-color: #DC3545 !important;
}

.status-success {
  color: #FFFFFF !important;
  background-color: #28a745 !important;
}

.status-error {
  color: #FFFFFF !important;
  background-color: #DC3545 !important;
}

.completed-bar-file-name {
  margin-top: 6px;
  font-size: 12px;
}

.completed-bar-text {
  color: #FFFFFF;
  margin-top: 6px;
  font-size: 12px;
  margin-left: 40px;
  font-style: italic;
  font-weight: bolder;
}

.completed-bar-icon {
  color: #FFFFFF;
  margin-top: 6px;
  font-size: 18px;
  margin-right: 40px;
  font-weight: bolder;

  &:hover {
    cursor: pointer;
  }
}

::v-deep .progress {
  border-radius: 0px !important;
  box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
}

::v-deep .bg-success {
  background-color: #DDDDDD !important;
}

.completed-bar {
  height: 32px;
  box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
}

.extra {
  box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
}

.button-upload-desc {
  color: #000000;
  background-color: #F2F2F2;
  box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
}

.upload-zone-row {
  margin-left: 1px;
  margin-right: 1px;
}

.upload-zone-col-left {
  border: 2px dashed #DDDDDD;
}

.upload-zone-col-left:hover {
  cursor: pointer !important;
};

.upload-zone-col-right {
  border: 2px dashed #DDDDDD;
}

.component-upload-file {
    .table {
        margin-bottom: 0 !important;
    }

    .zone-add {
        .btn-add {
            border: none;
            font-weight: bold;
            background-color: $west-side !important;
            background-color: $west-side;
            min-width: 120px;

            i {
                margin-right: 10px;
            }

            &:focus {
                box-shadow: none;
                background-color: $west-side !important;
            }

            &:active {
                box-shadow: none;
                background-color: $west-side !important;
            }

            &:active {
                &:focus {
                    box-shadow: none;
                }
            }
        }

        margin-bottom: 10px;
    }

    .zone-list-upload-file {
        .table-upload-file {
            &:not(:last-child) {
                margin-bottom: 15px;
            }

            .title-movie {
                margin-bottom: 10px;

                .zone-delete {
                    margin-bottom: 10px;

                    button {
                        border: none;
                        font-weight: bold;
                        background-color: $red-berry !important;
                        background-color: $red-berry;
                        min-width: 30px;
                        min-width: 120px;

                        i {
                            margin-right: 10px;
                        }

                        &:focus {
                            box-shadow: none;
                            background-color: $red-berry !important;
                        }

                        &:active {
                            box-shadow: none;
                            background-color: $red-berry !important;
                        }

                        &:active {
                            &:focus {
                                box-shadow: none;
                            }
                        }
                    }
                }

                label {
                    font-weight: bold;
                }
            }

            .upload-file {
                th {
                    background-color: $tolopea;
                    color: $white;
                    text-align: center;
                    vertical-align: middle;
                }

                td {
                    padding: 0.5rem;
                }

                th.th-type {
                    width: 130px;
                }

                td.td-type {
                    text-align: center;
                    vertical-align: middle;
                }
            }
        }
    }

    .zone-view-list-upload-file {
        .table-view-upload-file {
            &:not(:last-child) {
                margin-bottom: 15px;
            }

            .title-movie {
                margin-bottom: 10px;

                .zone-play {
                    margin-bottom: 10px;

                    button {
                        border: none;
                        font-weight: bold;
                        background-color: $red-berry !important;
                        background-color: $red-berry;
                        min-width: 30px;
                        min-width: 120px;

                        i {
                            margin-right: 10px;
                        }

                        &:focus {
                            box-shadow: none;
                            background-color: $red-berry !important;
                        }

                        &:active {
                            box-shadow: none;
                            background-color: $red-berry !important;
                        }

                        &:active {
                            &:focus {
                                box-shadow: none;
                            }
                        }
                    }
                }

                label {
                    font-weight: bold;
                }
            }

            .upload-file {
                th {
                    background-color: $tolopea;
                    color: $white;
                    text-align: center;
                    vertical-align: middle;
                }

                td {
                    padding: 0.5rem;
                }

                th.th-type {
                    width: 130px;
                }

                td.td-type {
                    text-align: center;
                    vertical-align: middle;
                }

                td.td-file {
                    text-align: center;
                    vertical-align: middle;
                }
            }
        }
    }
}
</style>
