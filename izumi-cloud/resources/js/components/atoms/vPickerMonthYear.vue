<template>
	<div class="picker-month-year">
		<div class="picker-month-year__back" :disabled="isDisable" @click="onClickBack()">
			<i class="fas fa-angle-left" />
		</div>
		<div class="picker-month-year__time">
			{{ textFull }}
		</div>
		<div class="picker-month-year__next" :disabled="isDisable" @click="onClickNext()">
			<i class="fas fa-angle-right" />
		</div>
	</div>
</template>

<script>
import { convertMonth, convertYear, getFullText } from '@/utils/convertTime';

export default {
    name: 'PickerMonthYear',
    props: {
        initMonth: {
            type: Number,
            default: null,
            validate: (value) => {
                return value >= 1 && value <= 12;
            },
        },
        initYear: {
            type: Number,
            default: null,
            validate: (value) => {
                return value >= 1;
            },
        },
        isDisable: {
            type: Boolean,
            default: false,
        },
        eventEmit: {
            type: String,
            default: 'PICKER_MONTH_YEAR_CHANGE',
            require: true,
        },
    },
    data() {
        return {
            isTime: {
                month: this.initData().month,
                year: this.initData().year,
            },
            textMonth: '',
            textYear: '',
            textFull: '',
        };
    },
    computed: {
        lang() {
            return this.$store.getters.language;
        },
    },
    watch: {
        isTime: {
            handler() {
                const DATA = {
                    month: this.isTime.month,
                    year: this.isTime.year,
                    textMonth: this.textMonth,
                    textYear: this.textYear,
                    textFull: this.textFull,
                };

                this.$bus.emit(this.eventEmit, DATA);
                this.getText();
            },
            deep: true,
        },
    },
    created() {
        const DATA = {
            month: this.isTime.month,
            year: this.isTime.year,
            textMonth: convertMonth(this.isTime.month, this.lang),
            textYear: convertYear(this.isTime.year, this.lang),
            textFull: getFullText(this.isTime.month, this.isTime.year, this.lang),
        };

        this.$bus.emit(this.eventEmit, DATA);
        this.getText();
    },
    methods: {
        initData() {
            let result = {
                year: '',
                month: '',
            };

            if (this.initYear && this.initMonth) {
                result = {
                    year: this.initYear,
                    month: this.initMonth,
                };
            } else {
                const d = new Date();

                const month = d.getMonth() + 1;
                const year = d.getFullYear();

                result = {
                    year: year,
                    month: month,
                };
            }

            return result;
        },

        getText() {
            this.textMonth = convertMonth(this.isTime.month, this.lang);
            this.textYear = convertYear(this.isTime.year, this.lang);
            this.textFull = getFullText(this.isTime.month, this.isTime.year, this.lang);
        },

        onClickBack() {
            if (this.isTime.month > 1) {
                this.isTime.month = this.isTime.month - 1;
            } else {
                this.isTime.month = 12;
                this.isTime.year = this.isTime.year - 1;
            }
        },

        onClickNext() {
            if (this.isTime.month < 12) {
                this.isTime.month = this.isTime.month + 1;
            } else {
                this.isTime.month = 1;
                this.isTime.year = this.isTime.year + 1;
            }
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    .picker-month-year {
        display: inline-flex;

        &__back,
        &__time,
        &__next {
            height: 35px;
            background-color: $tolopea;

            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 17px;
            color: $white;
        }

        &__back,
        &__next {
            cursor: pointer;
            width: 50px;

            &:hover {
                background-color: $west-side;
            }
        }

        &__back {
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
        }

        &__next {
            border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;
        }

        &__time {
            width: 100%;
            min-width: 100px;
        }

    }
</style>
