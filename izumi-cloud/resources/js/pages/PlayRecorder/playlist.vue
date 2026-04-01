<template>
	<b-overlay :show="overlay.show" :variant="overlay.variant" :opacity="overlay.opacity" :blur="overlay.blur" :rounded="overlay.sm">
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p class="text-loading">{{ $t('PLEASE_WAIT') }}</p>
			</div>
		</template>

		<div class="play-recorder">
			<b-container v-if="listRecorder.length">
				<div class="play-recorder__title">
					{{ title }}
				</div>

				<div class="play-recorder__view">
					<b-row class="mobile-video-play-card">
						<b-col v-for="(recorder, idx) in listRecorder" :key="idx" class="text-center" :cols="handleCol">
							<div class="item-video">
								<div class="type-video">
									{{ getTextTypeRecorder(recorder.type) }}
								</div>

								<div v-show="statusLoader[recorder.type] === 0" class="noti-load-faild">
									{{ $t('LOADED_RECORDER_FAILED') }}
								</div>

								<template v-if="list_movies.length !== 1">
									<video
										v-show="statusLoader[recorder.type] > 1"
										:id="recorder.type"
										:ref="recorder.type"
										autoplay
										playsinline="true"
										:loop="is_replay ? true : false"
										:muted="idx === 0 ? statusMute : true"
									>
										<source :src="recorder.url">
										Your browser does not support HTML video.
									</video>
								</template>

								<template v-else>
									<video v-show="statusLoader[recorder.type] > 1" :id="recorder.type" :ref="recorder.type" autoplay :muted="idx === 0 ? statusMute : true" loop playsinline="true">
										<source :src="recorder.url">
										Your browser does not support HTML video.
									</video>
								</template>
							</div>
						</b-col>
					</b-row>
				</div>

				<div class="play-recorder__control">
					<div class="time-line">
						<input
							id="time-line-viewed"
							v-model="numTimeLine"
							:min="0"
							:max="numTotalTimeVideo"
							:step="0.000001"
							type="range"
							name="time-line-viewed"
							:style="handleTimeLine(numTimeLine, numTotalTimeVideo)"
							@mousedown="onMouseDown()"
							@mouseup="onMouseUp()"
						>
					</div>

					<b-row>
						<b-col class="text-center" cols="12" sm="12" md="12" xl="3" lg="3">
							<div class="show-time">
								{{ textCurrentTimeVideo }} / {{ textTotalTimeVideo }}
							</div>
						</b-col>

						<b-col cols="12" sm="12" md="12" xl="6" lg="6">
							<div class="show-control">
								<div class="btn-back">
									<i class="fas fa-chevron-double-left" @click="onClickBack(5)" />
								</div>

								<div class="btn-back">
									<i class="fas fa-chevron-left" @click="onClickBack(1)" />
								</div>

								<div class="btn-play" @click="onClickButtonStatus()">
									<i v-show="status === false" ref="playButton" class="fas fa-play" />
									<i v-show="status === true" class="fas fa-pause" />
								</div>

								<div class="btn-next">
									<i class="fas fa-chevron-right" @click="onClickNext(1)" />
								</div>

								<div class="btn-next">
									<i class="fas fa-chevron-double-right" @click="onClickNext(5)" />
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row class="pb-3">
						<b-col cols="3" />

						<b-col class="text-center" cols="12" sm="12" md="12" xl="6" lg="6">
							<div class="show-control">
								<div class="btn-next">
									<i class="fas fa-reply-all" @click="handleReturnToStart()" />
								</div>

								<div class="btn-next">
									<i v-if="is_replay" class="fas fa-repeat-1-alt" @click="handleReplay(false)" />
									<i v-else class="fas fa-repeat-alt" @click="handleReplay(true)" />
								</div>

								<div class="btn-next">
									<i v-if="statusMute" class="fas fa-volume-mute" @click="handleVolume(false)" />
									<i v-else class="fas fa-volume-up" @click="handleVolume(true)" />
								</div>

								<div class="btn-next" @click="handleSkipPlaylist()">
									<i class="fas fa-forward" />
								</div>
							</div>
						</b-col>

						<b-col cols="3" />
					</b-row>
				</div>
			</b-container>
		</div>
	</b-overlay>
</template>

<script>
const URL_API = {
    detailRecorder: '/driver-recorder-play-list-viewer',
};

import { getDetailFile } from '@/api/modules/driverRecorder';

export default {
    name: 'Playlist',
    data() {
        return {
            status: false,
            statusMute: true,
            ended: false,
            textCurrentTimeVideo: '00:00:00',
            textTotalTimeVideo: '00:00:00',
            numCurrentTimeVideo: 0,
            numTotalTimeVideo: 0,
            numTimeLine: 0,
            mouseTimeLine: false,

            title: '',
            listRecorder: [],

            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

            id: null,
            idxRecorder: null,
            idxRecorderMax: null,

            statusLoader: {
                front: null,
                inside: null,
                behind: null,
            },

            list_movies: [],

            current_index: parseInt(this.$store.getters.current_index_recorder) || 0,

            is_replay: false,
        };
    },
    computed: {
        handleCol() {
            return this.listRecorder.length > 1 ? 6 : 12;
        },
        language() {
            return this.$store.getters.language;
        },
    },
    watch: {
        numTimeLine() {
            if (this.mouseTimeLine) {
                this.setCurrentTime();
            }
        },
    },
    async mounted() {
        await this.initData();

        const listRef = this.getAllRefName();

        const len = listRef.length;
        let idx = 0;

        while (idx < len) {
            this.$refs[listRef[idx]][0].onloadedmetadata = () => {
                this.textTotalTimeVideo = this.handleCaletextTotalTimeVideo();
            };

            document.getElementById(listRef[idx]).addEventListener('loadeddata', () => {
                const allRef = this.getAllRefName();

                const lenRef = allRef.length;
                let idxRef = 0;

                while (idxRef < lenRef) {
                    this.statusLoader[allRef[idxRef]] = this.$refs[allRef[idxRef]][0].readyState;

                    idxRef++;
                }
            });

            document.getElementById(listRef[idx]).addEventListener('timeupdate', () => {
                let listRef = this.getAllRefName();

                listRef = listRef.filter((item) => {
                    return this.$refs[item][0].readyState >= 1;
                });

                this.textCurrentTimeVideo = this.convertTime(document.getElementById(listRef[this.idxRecorderMax]).currentTime);
                this.numCurrentTimeVideo = document.getElementById(listRef[this.idxRecorderMax]).currentTime;
                this.numTimeLine = document.getElementById(listRef[this.idxRecorderMax]).currentTime;
            });

            document.getElementById(listRef[idx]).addEventListener('ended', (e) => {
                let allRef = this.getAllRefName();

                allRef = allRef.filter((item) => {
                    return this.$refs[item][0].readyState >= 1;
                });

                if (e.srcElement.id === allRef[this.idxRecorderMax]) {
                    this.status = false;

                    if (!this.is_replay) {
                        if (this.current_index < this.list_movies.length - 1) {
                            this.current_index = this.current_index + 1;
                        } else {
                            this.current_index = 0;
                        }

                        this.$store.dispatch('driver_recorder/setCurrentIndexRecorder', this.current_index);

                        const route = this.$router.resolve({ path: `/playlist/${this.$route.params.id}` });

                        window.location.href = route.href;
                    }
                }
            }, false);

            idx++;
        }

        this.status = true;
        this.playRecorder();
    },
    methods: {
        async initData() {
            this.overlay.show = true;

            await this.handleGetRecorder();

            this.overlay.show = false;
        },
        async handleGetRecorder() {
            try {
                this.listRecorder = [];

                const ID = this.$route.params.id;

                this.id = ID;

                if (ID) {
                    const URL = `${URL_API.detailRecorder}/${ID}`;

                    const { code, data } = await getDetailFile(URL);

                    if (code === 200) {
                        let RECORD = null;

                        this.list_movies = data.play_list_recorder;

                        RECORD = data.play_list_recorder[this.current_index];

                        this.title = `${RECORD['record_date']} - ${RECORD['department_name']} - ${RECORD ? `${RECORD['movie_title']}` : ''} を再生しています。`;

                        if (RECORD.front) {
                            this.listRecorder.push({
                                type: 'front',
                                url: RECORD.front.file_url,
                            });
                        }

                        if (RECORD.inside) {
                            this.listRecorder.push({
                                type: 'inside',
                                url: RECORD.inside.file_url,
                            });
                        }

                        if (RECORD.behind) {
                            this.listRecorder.push({
                                type: 'behind',
                                url: RECORD.behind.file_url,
                            });
                        }
                    }
                }
            } catch (err) {
                console.log(err);
            }
        },
        getTextTypeRecorder(type) {
            const LIBRARY = {
                en: {
                    front: 'Front',
                    inside: 'Inside',
                    behind: 'Behind',
                },
                ja: {
                    front: '前方',
                    inside: '車内',
                    behind: '後方',
                },
            };

            return LIBRARY[this.language || 'ja'][type] || '';
        },
        async onClickButtonStatus() {
            this.status = !this.status;

            await this.$store.dispatch('driver_recorder/setAutoplayStatus', this.status);

            if (this.status) {
                this.playRecorder();
            } else {
                this.pauseRecorder();
            }
        },
        getAllRefName() {
            const len = this.listRecorder.length;
            let idx = 0;
            const result = [];

            while (idx < len) {
                result.push(this.listRecorder[idx].type);

                idx++;
            }

            return result;
        },
        onClickBack(sec) {
            let BACK_TIME = this.numTimeLine - sec;

            if (BACK_TIME < 0) {
                BACK_TIME = 0;
            }

            this.numTimeLine = BACK_TIME;
            this.setCurrentTime();
        },
        onClickNext(sec) {
            let NEXT_TIME = this.numTimeLine + sec;

            if (NEXT_TIME < 0) {
                NEXT_TIME = 0;
            }

            this.numTimeLine = NEXT_TIME;
            this.setCurrentTime();
        },
        playRecorder() {
            let listRef = this.getAllRefName();

            listRef = listRef.filter((item) => {
                return this.$refs[item][0].readyState >= 1;
            });

            const len = listRef.length;
            let idx = 0;
            let count = 0;

            while (idx < len) {
                if (this.numCurrentTimeVideo < this.$refs[listRef[idx]][0].duration) {
                    count = count + 1;
                    this.$refs[listRef[idx]][0].play();
                }

                idx++;
            }

            if (count === 0) {
                idx = 0;

                while (idx < len) {
                    this.numTimeLine = 0;
                    this.numCurrentTimeVideo = 0;
                    this.$refs[listRef[idx]][0].play();

                    idx++;
                }
            }
        },
        pauseRecorder() {
            let listRef = this.getAllRefName();
            listRef = listRef.filter((item) => {
                return this.$refs[item][0].readyState >= 1;
            });

            const len = listRef.length;
            let idx = 0;

            while (idx < len) {
                this.$refs[listRef[idx]][0].pause();

                idx++;
            }
        },
        handleVolume(status) {
            this.statusMute = status;

            let listRef = this.getAllRefName();

            listRef = listRef.filter((item) => {
                return this.$refs[item][0].readyState >= 1;
            });

            if (status) {
                this.$refs[listRef[0]][0].muted = false;
                this.$refs[listRef[0]][0].volume = 1.0;
            } else {
                this.$refs[listRef[0]][0].muted = true;
            }
        },
        onMouseDown() {
            this.status = false;
            this.mouseTimeLine = true;
            this.pauseRecorder();
        },
        onMouseUp() {
            this.mouseTimeLine = false;
        },
        setCurrentTime() {
            let listRef = this.getAllRefName();

            listRef = listRef.filter((item) => {
                return this.$refs[item][0].readyState >= 1;
            });

            const len = listRef.length;
            let idx = 0;

            while (idx < len) {
                this.$refs[listRef[idx]][0].currentTime = this.numTimeLine;

                idx++;
            }
        },
        handleCaletextTotalTimeVideo() {
            let listRef = this.getAllRefName();

            listRef = listRef.filter((item) => {
                return this.$refs[item][0].readyState >= 1;
            });

            const len = listRef.length;
            let idx = 0;
            let max = null;

            while (idx < len) {
                if (idx === 0) {
                    max = this.$refs[listRef[idx]][0].duration;
                    this.idxRecorderMax = 0;
                } else {
                    if (max < this.$refs[listRef[idx]][0].duration) {
                        max = this.$refs[listRef[idx]][0].duration;
                        this.idxRecorderMax = idx;
                    }
                }

                idx++;
            }

            if (max) {
                this.numTotalTimeVideo = max;
                return this.convertTime(max);
            }

            return '00:00:00';
        },
        convertTime(time) {
            time = Number(time);
            const h = Math.floor(time / 3600);
            const m = Math.floor((time % 3600) / 60);
            const s = Math.floor((time % 3600) % 60);

            return `${this.format2Digit(h)}:${this.format2Digit(m)}:${this.format2Digit(s)}`;
        },
        format2Digit(number) {
            return number < 10 ? `0${number}` : `${number}`;
        },
        handleTimeLine(value, max) {
            return `background: linear-gradient(to right, #f00 0%, #f00 ${this.calPercent(value, max)}%, #fff ${this.calPercent(value, max)}%, white 100%)`;
        },
        calPercent(value, max) {
            return (100 * value) / max;
        },
        handleReplay(status) {
            this.is_replay = status;
        },
        handleSkipPlaylist() {
            if (this.current_index < this.list_movies.length - 1) {
                this.current_index = this.current_index + 1;
            } else {
                this.current_index = 0;
            }

            this.$store.dispatch('driver_recorder/setCurrentIndexRecorder', this.current_index);

            const route = this.$router.resolve({ path: `/playlist/${this.$route.params.id}` });

            window.location.href = route.href;
        },
        handleReturnToStart() {
            this.numTimeLine = 0;
            this.setCurrentTime();
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables';

.play-recorder {
  width: 100vw;
  min-height: 100vh;
  background-color: $black;

  &__title {
    color: $white;
    margin-bottom: 1rem;
    padding-top: 1rem;
    font-size: 1.5rem;
    font-weight: bold;
  }

  &__view {
    .item-video {
      border: 2px solid $white;
      margin-bottom: 1rem;
      border-radius: 5px;
      overflow: hidden;

      .type-video {
        text-align: left;
        color: $white;
        font-weight: bold;
        margin-bottom: 5px;
        background-color: $west-side;
        padding: 7px;
        text-indent: 10px;
      }

      video {
        width: 100%;
        min-height: 300px;
        max-height: 300px;
        padding: 5px 10px;
      }

      .noti-load-faild {
        width: 100%;
        min-height: 306px;
        max-height: 306px;
        padding: 5px 10px;

        color: $white;
        font-weight: 600;

        display: flex;
        align-items: center;
        justify-content: center;
      }
    }
  }

  &__control {
    color: $white;

    .time-line {
      height: 30px;
      margin-top: 20px;
      position: relative;
    }

    #time-line-viewed {
      width: 100%;
      height: 8px;
      outline: none;
      border-radius: 4px;
      background: #59CE8F;
      -webkit-appearance: none;
      transition: background 450ms ease-in;
    }

    input[type="range"]::-webkit-slider-runnable-track {
      height: 8px;
      border-radius: 4px;
      background: transparent;
      -webkit-appearance: none;
    }

    input[type="range"]::-webkit-slider-thumb {
      top: -4px;
      width: 16px;
      height: 16px;
      cursor: pointer;
      border-radius: 50%;
      position: relative;
      background: #59CE8F;
      -webkit-appearance: none;
      transition: background .3s ease-in-out;
    }

    input[type="range"]::-webkit-slider-thumb:hover {
      background: #ff6347;
    }

    .show-time {
      margin-top: 10px;
      line-height: 58px;
    }

    .show-control {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 10px;

      border: 2px solid $white;
      border-radius: 20px;
      padding: 10px 0;

      .btn-back {
        font-size: 1.4rem;
        cursor: pointer;

        margin: 0 1rem;

        &:hover {
          color: #f00;
        }
      }

      .btn-next {
        font-size: 1.4rem;
        cursor: pointer;

        margin: 0 1rem;

        &:hover {
          color: #f00;
        }
      }

      .btn-play {
        font-size: 1.4rem;
        cursor: pointer;

        margin: 0 1rem;
        color: #f00;

        &:hover {
          color: #f00;
        }
      }
    }

    .control-audio {
      margin-top: 10px;
      line-height: 53px;

      i {
        font-size: 1.4rem;
        cursor: pointer;

        margin: 0 1rem;

        &:hover {
          color: #f00;
        }
      }
    }
  }
}

@media (min-width: 0) and (max-width: 576px) {
  video {
    width: 100%;
  }

  .noti-load-faild {
    width: 100%;
    min-height: 156px !important;
    max-height: 156px !important;
    padding: 5px 10px;

    color: $white;
    font-weight: 600;

    display: flex;
    align-items: center;
    justify-content: center;
  }

  .mobile-video-play-card {
    flex-direction: column !important;

    .col-6 {
      max-width: 100% !important;
    }

    .item-video {
      border: none !important;
      padding: 0px !important;
      margin-bottom: 0px !important;

      & > video {
        padding: 0px !important;
        min-height: 100% !important;
        max-height: 100% !important;
      }

      .type-video {
        padding: 0px !important;
        text-indent: 0px !important;
        background-color: #000000 !important;
      }
    }
  }
}

@media (min-width: 576px) and (max-width: 768px) {
  video {
    width: 100%;
  }

  .noti-load-faild {
    width: 100%;
    min-height: 156px !important;
    max-height: 156px !important;
    padding: 5px 10px;

    color: $white;
    font-weight: 600;

    display: flex;
    align-items: center;
    justify-content: center;
  }

  .mobile-video-play-card {
    flex-direction: column !important;

    .col-6 {
      max-width: 100% !important;
    }

    .item-video {
      border: none !important;
      padding: 0px !important;
      margin-bottom: 0px !important;

      & > video {
        padding: 0px !important;
        min-height: 100% !important;
        max-height: 100% !important;
      }

      .type-video {
        padding: 0px !important;
        text-indent: 0px !important;
        background-color: #000000 !important;
      }
    }
  }
}

@media (min-width: 768px) and (max-width: 992px) {
  video {
    width: 100%;
  }

  .noti-load-faild {
    width: 100%;
    min-height: 206px !important;
    max-height: 206px !important;
    padding: 5px 10px;

    color: $white;
    font-weight: 600;

    display: flex;
    align-items: center;
    justify-content: center;
  }

  .mobile-video-play-card {
    flex-direction: column !important;

    .col-6 {
      max-width: 100% !important;
    }

    .item-video {
      border: none !important;
      padding: 0px !important;
      margin-bottom: 0px !important;

      & > video {
        padding: 0px !important;
        min-height: 100% !important;
        max-height: 100% !important;
      }

      .type-video {
        padding: 0px !important;
        text-indent: 0px !important;
        background-color: #000000 !important;
      }
    }
  }
}
</style>
