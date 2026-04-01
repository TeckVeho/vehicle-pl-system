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

		<div class="driver-recorder-list">
			<div class="driver-recorder-list__title-header">
				<vHeaderPage>{{ $t('PAGE_TITLE.DRIVER_RECONRDER_LIST') }}</vHeaderPage>
			</div>

			<div class="driver-recorder-list__filter">
				<FilterListDriverRecorder :list-department="listDepartment" :list-type="listType" />
			</div>

			<div class="driver-recorder-list__select-year-month">
				<b-row class="mb-3">
					<b-col class="col-sm-6">
						<vPickerMonthYear :event-emit="eventEmitPickerYearMonth" :init-year="pickerYearMonth.year" :init-month="pickerYearMonth.month" />
					</b-col>

					<b-col class="col-sm-6 text-right">
						<b-button class="btn-playlist" @click="handleShowModalPlaylistDetail()">
							<span>{{ 'プレイリスト' }}</span>
						</b-button>
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
						<span>{{ convertType(data.item.type_two, 2) }}</span>
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

					<template #cell(download)="download">
						<i class="fas fa-arrow-to-bottom" @click="onClickDownload(download.item.id)" />
					</template>

					<template #cell(detail)="detail">
						<i class="fas fa-eye" @click="onClickDetail(detail.item.id)" />
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
		</div>

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
											:class="{ [`cursor-grabbing`]: drag === true }"
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
	</b-overlay>
</template>

<script>
import { obj2Path } from '@/utils/obj2Path';
import { cleanObj } from '@/utils/handleObj';
import { MakeToast } from '@/utils/MakeToast';

import axios from 'axios';
import draggable from 'vuedraggable';
import vHeaderPage from '@/components/atoms/vHeaderPage';
import vPagination from '@/components/atoms/vPagination';
import PickerMonthYear from '@/components/atoms/vPickerMonthYear';
import FilterListDriverRecorder from '@/pages/DriverRecorder/components/FilterListDriverRecorder.vue';

const URL_API = {
    apiGetListFile: '/api/driver-recorder-viewer',
    apiGetListDepartment: '/api/department/list-all',
    apiGetPlaylist: '/api/driver-recorder-play-list-viewer',
    apiChangeOrder: '/api/driver-play-list/update-position-for-user',
};

export default {
    name: 'PublicDriverRecorder',
    components: {
        draggable,
        vHeaderPage,
        vPagination,
        FilterListDriverRecorder,
        vPickerMonthYear: PickerMonthYear,
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

            eventEmitPickerYearMonth: 'DRIVER_RECORDER_PICKER_YEAR_MONTH_CHANGE',

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

            playlist_items: [],

            showModalPlaylistDetail: false,

            list_new_order: [],

            drag: false,

            list_temp_driver_recorder: [],
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
                { key: 'detail', sortable: false, label: this.$t('DRIVER_RECORDER.TABLE_DETAIL'), thClass: 'th-detail' },
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
    },
    created() {
        this.initData();
    },
    destroyed() {
        this.destroyedEventBus();
    },
    methods: {
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

                await axios.put(URL, DATA);
            } catch (error) {
                console.log(error);
            }
        },
        createdEventBus() {
            this.$bus.on('DRIVER_RECORDER_FILTER_DATA', filter => {
                this.filter = filter;
            });

            this.$bus.on('DRIVER_RECORDER_FILTER_APPLY', () => {
                this.handleGetListFile();
            });

            this.$bus.on(this.eventEmitPickerYearMonth, value => {
                this.pickerYearMonth = value;
                this.$store.dispatch('driverRecorder/setYearMonth', value);
                this.handleGetListFile();
            });
        },
        destroyedEventBus() {
            this.$bus.off('DRIVER_RECORDER_FILTER_DATA');
            this.$bus.off('DRIVER_RECORDER_FILTER_APPLY');
            this.$bus.off(this.eventEmitPickerYearMonth);
        },
        async initData() {
            try {
                this.overlay.show = true;

                await this.createdEventBus();

                await this.handleGetListDepartment();

                this.overlay.show = false;
            } catch (err) {
                console.log(err);
            }
        },
        async handleGetListFile() {
            this.overlay.show = true;

            try {
                let PARAMS = {
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    department_id: this.filter.department.value,
                    record_date: this.filter.accidentDate.value,
                    type_one: this.filter.type_one.value,
                    type_two: this.filter.type_two.value,
                    shipper: this.filter.shipper.value,
                    accident_classification: this.filter.accident_classification.value,
                    place_of_occurrence: this.filter.place_of_occurrence.value,
                    title: this.filter.title.value,
                    month: `${this.pickerYearMonth.year}-${this.handleFormatMonth(this.pickerYearMonth.month)}`,
                    sort_by: this.filterQuery.sort_by,
                    sort_type: this.filterQuery.sort_type,
                };

                PARAMS = cleanObj(PARAMS);

                const URL = `${URL_API.apiGetListFile}?${obj2Path(PARAMS)}`;

                const response = await axios.get(URL, PARAMS);

                if (response.status === 200) {
                    this.items = response.data.result;
                    this.pagination.total_rows = response.data.pagination.total_records;
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
                const response = await axios.get(URL_API.apiGetListDepartment);

                if (response.status === 200) {
                    this.listDepartment = response.data.data;
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
            this.filterQuery.sort_by = ctx.sortBy === 'department_id' ? 'department_id' : ctx.sortBy;
            this.filterQuery.sort_by = ctx.sortBy === 'type_one' ? 'type_one' : ctx.sortBy;
            this.filterQuery.sort_by = ctx.sortBy === 'type_two' ? 'type_two' : ctx.sortBy;
            this.filterQuery.sort_by = ctx.sortBy === 'shipper' ? 'shipper' : ctx.sortBy;
            this.filterQuery.sort_by = ctx.sortBy === 'accident_classification' ? 'accident_classification' : ctx.sortBy;
            this.filterQuery.sort_by = ctx.sortBy === 'place_of_occurrence' ? 'place_of_occurrence' : ctx.sortBy;

            this.filterQuery.sort_type = (ctx.sortDesc === true) ? 'asc' : 'desc';

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
        async handlePlayRecorder(id, idx) {
            if (id) {
                const URL = `${window.origin}/public-play-recorder/${id}/${idx}`;
                window.open(URL);
            }
        },
        async onClickDownload(id) {
            try {
                const URL = `${window.origin}/api/driver-recorder/download/${id}`;

                window.open(URL);
            } catch (error) {
                console.log(error);
            }

            this.overlay.show = false;
        },
        onClickDetail(id) {
            this.$router.push({ name: 'PublicDriverRecorderDetail', params: { id }});
        },
        handleFormatMonth(_month) {
            return _month < 10 ? `0${_month}` : _month;
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
        handleNavigateToPlaylistScreen(id) {
            const URL = `${window.origin}/public-playlist/${id}`;
            window.open(URL, '_blank');
        },
    },
};
</script>

<style lang="scss">
.modal-detail-playlist-header {
  color: #FFFFFF !important;
  background-color: #343a40 !important;
}
</style>

<style lang="scss" scoped>
@import '@/scss/variables';

.handle:hover {
  cursor: pointer;
}

.modal-header-card {
  width: 100%;
  display: flex;
  font-size: 20px;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;

  & > i:hover {
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
  };
}

.info-card {
  width: 100%;
  display: flex;
  align-items: center;
  flex-direction: row;
  justify-content: space-between;
  border-top: 1px dashed #dddddd;
  border-bottom: 1px dashed #dddddd;

  & > span {
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

.btn-playlist {
  width: 180px;
  height: 35px;
  font-weight: bold;
  background-color: #ff8a20;

  &:hover {
    opacity: .6;
  }
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
    padding: 0px 20px;
    margin-bottom: 10px;
  }

  &__table-data {
    overflow: auto;
    max-height: calc(100vh - 370px);

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
    padding: 0px 20px;

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
</style>
