<template>
	<div class="main-content">
		<div class="uppder">
			<button v-if="currentIndex !== 0" class="carousel-button left" @click="prevSlide">❮</button>

			<template v-if="listFiles.length > 0 && listFiles[currentIndex]?.file && listFiles[currentIndex]?.file?.file_url">
				<img :src="listFiles[currentIndex].file.file_url" style="width: 100%; height: 100%; object-fit: contain;">
			</template>

			<button v-if="currentIndex !== listFiles.length - 1" class="carousel-button right" @click="nextSlide">❯</button>
		</div>

		<div class="bottom">
			<input v-model="currentIndex" type="range" min="0" :max="listFiles.length - 1" class="slider-control w-100" @input="moveToSlide(currentIndex)">
			<span style="font-weight: bold; margin-top: 20px;">{{ currentIndex + 1 }} / {{ listFiles.length }} </span>
		</div>
	</div>
</template>

<script>
const axios = require('axios').default;

const urlAPIs = {
    apiGetListPocketBook: '/api/pocket-book',
};

export default {
    name: 'PreviewPDF',
    data() {
        return {
            listFiles: [],
            currentIndex: 0,

            startX: 0,
        };
    },
    computed: {
        token() {
            return this.$route.query.token;
        },
        tag() {
            return this.$route.query.tag;
        },
    },
    mounted() {
        const swipeArea = this.$el.querySelector('.uppder');

        if (swipeArea) {
            swipeArea.addEventListener('touchstart', this.handleTouchStart, { passive: true }); // don't prevent default yet
            swipeArea.addEventListener('touchend', this.handleTouchEnd, { passive: true }); // safer than touchmove
        }
    },
    beforeDestroy() {
        const swipeArea = this.$el.querySelector('.uppder');

        if (swipeArea) {
            swipeArea.removeEventListener('touchstart', this.handleTouchStart);
            swipeArea.removeEventListener('touchend', this.handleTouchEnd);
        }
    },
    created() {
        this.handleGetPocketBookList();
    },
    methods: {
        async handleGetPocketBookList() {
            if (this.token) {
                const HEADERS = {
                    'Authorization': this.token,
                    'Content-Type': 'application/json',
                };

                const PARAMS = {
                    year: new Date().getFullYear(),
                };

                try {
                    const response = await axios.get(urlAPIs.apiGetListPocketBook, { headers: HEADERS, params: PARAMS });

                    const { code, data } = response.data;

                    if (code === 200) {
                        const filteredData = data.filter(item => {
                            return item.file !== null && parseInt(item.tag) === parseInt(this.tag);
                        });

                        this.listFiles = [...filteredData];
                    }
                } catch (error) {
                    console.log(error);
                }
            } else {
                console.log('=============================================================');
                console.log('[51] -> [NO TOKEN PROVIDED] ==>', '[NO TOKEN PROVIDED]');
                console.log('=============================================================');
            }
        },
        nextSlide() {
            this.currentIndex = (this.currentIndex + 1) % this.listFiles.length;
        },
        prevSlide() {
            this.currentIndex = (this.currentIndex - 1 + this.listFiles.length) % this.listFiles.length;
        },
        moveToSlide(index) {
            this.currentIndex = index % this.listFiles.length;
        },
        handleTouchStart(e) {
            this.startX = e.touches[0].clientX;
        },
        handleTouchEnd(e) {
            const endX = e.changedTouches[0].clientX;
            const deltaX = endX - this.startX;

            if (Math.abs(deltaX) > 30) {
                if (deltaX < 0 && this.currentIndex < this.listFiles.length - 1) {
                    this.nextSlide(); // swipe left
                } else if (deltaX > 0 && this.currentIndex > 0) {
                    this.prevSlide(); // swipe right
                }
            }
        },
    },
};
</script>

<style lang="scss" scoped>
html, body {
    margin: 0;
    height: 100%;
}

.main-content {
    height: 100vh;
    min-height: -webkit-fill-available;
}

.my-slider {
    display: flex;
    align-items: center;
    justify-content: center;
}

.carousel-button {
    z-index: 10;
    color: white;
    border: none;
    font-size: 2rem;
    padding: 0.5rem;
    cursor: pointer;
    position: absolute;
    border-radius: 10px;
    background-color: rgba(0, 0, 0, 0.15);
}

.carousel-button.left {
    left: 10px;
}

.carousel-button.right {
    right: 10px;
}

.uppder {
    height: 85%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #333333;
}

.bottom {
    width: 100%;
    height: 15%;
    display: flex;
    padding: 0.5rem;
    margin-top: 1rem;
    align-items: center;
    flex-direction: column;
    justify-content: flex-start;
}

.slider-control {
    width: 100%;
    height: 15px;
    outline: none;
    appearance: none;
    border-radius: 15px;
    background: #333333;
    -webkit-appearance: none;
    transition: background 0.3s ease;
}

.slider-control::-webkit-slider-thumb {
    width: 30px;
    height: 30px;
    cursor: pointer;
    appearance: none;
    border-radius: 50%;
    background: #A0C878;
    -webkit-appearance: none;
}

/* For Firefox */
.slider-control::-moz-range-thumb {
    width: 30px;
    height: 30px;
    cursor: pointer;
    border-radius: 50%;
    background: #A0C878;
}

.slider-control:hover,
.slider-control:focus {
    background: #aaa;
}

.slider-control:active::-webkit-slider-thumb {
    background: #A0C878;
}

.slider-control:active::-moz-range-thumb {
    background: #A0C878;
}

html, body, button {
    touch-action: none;
    overflow-x: hidden;
    overscroll-behavior-x: none;
}
</style>
