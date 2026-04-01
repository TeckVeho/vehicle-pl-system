<template>
	<b-overlay :show="overlay.show" :variant="overlay.variant" :opacity="overlay.opacity" :blur="overlay.blur" :rounded="overlay.sm">
		<!-- Template overlay -->
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
									<video v-show="statusLoader[recorder.type] > 1" :id="recorder.type" :ref="recorder.type" autoplay :muted="idx === 0 ? statusMute : true" playsinline="true">
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

					<b-row class="pb-3">
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

								<div class="btn-next">
									<i v-if="statusMute" class="fas fa-volume-mute" @click="handleVolume(false)" />
									<i v-else class="fas fa-volume-up" @click="handleVolume(true)" />
								</div>
							</div>
						</b-col>
					</b-row>
				</div>
			</b-container>
		</div>
	</b-overlay>
</template>

<script>
const URL_API = {
    detailRecorder: '/api/driver-recorder-viewer',
};

import axios from 'axios';

export default {
    name: 'PublicPlayRecorder',
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

                this.status = true;
                this.playRecorder();
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

                    const next_idx_recorder = this.idxRecorder + 1;

                    const idx = next_idx_recorder < this.list_movies.length ? next_idx_recorder : 0;

                    const route = this.$router.resolve({ path: `/public-play-recorder/${this.$route.params.id}/${idx}` });

                    window.location.href = route.href;
                }
            }, false);

            idx++;
        }
    },
    methods: {
        async initData() {
            this.overlay.show = true;

            await this.handleGetRecorder();

            this.overlay.show = false;
        },
        async handleGetRecorder() {
            try {
                const ID = parseInt(this.$route.params['id']);
                const INDEX = parseInt(this.$route.params['index']);

                this.id = ID;
                this.idxRecorder = INDEX;

                if (ID) {
                    const URL = `${URL_API.detailRecorder}/${ID}`;

                    const response = await axios.get(URL);

                    const DATA = response.data.data;

                    if (response.status === 200) {
                        let RECORD = null;

                        if (this.idxRecorder >= 0 && this.idxRecorder < DATA.list_recorder.length) {
                            RECORD = DATA.list_recorder[this.idxRecorder];
                        }

                        this.list_movies = DATA.list_recorder;

                        this.title = `${DATA.record_date} - ${DATA.department_name} - ${RECORD ? `${RECORD.movie_title}` : ''} を再生しています。`;

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
        onClickButtonStatus() {
            this.status = !this.status;

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
      #time-line-viewed {
        height: 8px;
        width: 100%;
        outline: none;
        transition: background 450ms ease-in;
        -webkit-appearance: none;
        margin-top: 20px;
      }

      input[type=”range”]::-webkit-slider-runnable-track {
        border-radius: 0;
      }

      input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        height: 8px;
        width: 10px;
        border-radius: 0;
        background: #59CE8F;
        cursor: pointer;
        transition: background .3s ease-in-out;
      }
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
