<template>
	<div class="insurance-rate-master-edit">
		<b-overlay
			:show="overlay.show"
			:blur="overlay.blur"
			:rounded="overlay.sm"
			:variant="overlay.variant"
			:opacity="overlay.opacity"
			style="height: 100vh;"
		>
			<template #overlay>
				<div class="text-center">
					<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
					<p style="margin-top: 10px">{{ $t("PLEASE_WAIT") }}</p>
				</div>
			</template>

			<div class="header">
				<vHeaderPage>{{ $t("INSURANCE_RATE_MASTER") }}</vHeaderPage>
			</div>

			<b-row class="main-content">
				<b-col cols="12">
					<label for="kinds">{{ $t('INSURANCE_RATE_MASTER_SCREEN.KINDS') }}</label>
					<b-form-input v-model="kinds" type="text" readonly />
				</b-col>

				<b-col cols="12" class="mt-3">
					<label for="name">{{ $t('INSURANCE_RATE_MASTER_SCREEN.RATE_NAME') }}</label>
					<b-form-input v-model="name" type="text" readonly />
				</b-col>

				<b-col cols="12" class="mt-3">
					<label for="current-rate">{{ $t('INSURANCE_RATE_MASTER_SCREEN.CHANGE_BEFORE') }}</label>
					<b-form-input v-model="current_rate" type="text" readonly />
				</b-col>

				<b-col cols="12" class="mt-3">
					<label for="change-rate">{{ $t('INSURANCE_RATE_MASTER_SCREEN.AFTER_CHANGE') }}</label>
					<b-form-input v-model="change_rate" type="text" />
				</b-col>

				<b-col cols="12" class="mt-3">
					<label for="applicable-date">{{ $t('INSURANCE_RATE_MASTER_SCREEN.APPLICATION_DATE') }}</label>

					<!-- <b-form-datepicker
						v-model="applicable_date"
						locale="ja"
						:label-help="$t('DATE_PICKER_LABEL_HELP')"
						:label-no-date-selected="$t('NO_DATE_SELECTED')"
						:date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
						/> -->

					<Datepicker
						v-model="applicable_date"
						:language="ja"
						format="yyyy-MM"
						:placeholder="$t('NO_DATE_SELECTED')"
						input-class="year-month-picker"
						:disabled-dates="disabledDates"
						:open-date="defaultDate"
						@changedMonth="onMonthChanged"
					/>
				</b-col>
			</b-row>

			<b-row class="footer mt-3">
				<b-col cols="6">
					<b-button class="button-back" @click="navigateToIndexScreen()">
						<span>{{ $t('BUTTON.BACK') }}</span>
					</b-button>
				</b-col>

				<b-col cols="6" class="v-button-default">
					<b-button class="button-save" @click="onClickSave()">
						<span>{{ $t('BUTTON.SAVE') }}</span>
					</b-button>
				</b-col>
			</b-row>
		</b-overlay>
	</div>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import Datepicker from 'vuejs-datepicker';

import { ja } from 'vuejs-datepicker/dist/locale';
import { getInsuranceRateDetail, putInsuranceRate } from '@/api/modules/insuranceRateMaster';

const urlAPI = {
    urlGetInsuranceRateDetail: '/insurance-rate',
    urlPutInsuranceRate: '/insurance-rate',
};

export default {
    name: 'InsuranceRateMasterEdit',
    components: {
        Datepicker,
        vHeaderPage,
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

            kinds: '',
            name: '',
            current_rate: '',
            change_rate: '',
            applicable_date: '',

            ja: ja,

            disabledDates: {
                customPredictor(date) {
                    if (date.getDate() !== 1){
                        return true;
                    }
                },
            },
        };
    },
    computed: {
        defaultDate() {
            const today = new Date();
            return new Date(today.getFullYear(), today.getMonth(), 1);
        },
    },
    created() {
        this.getOneInsuranceRate();
    },
    methods: {
        async getOneInsuranceRate() {
            this.overlay.show = true;

            const INSURANCE_ID = this.$route.params.id;

            if (INSURANCE_ID) {
                const URL = `${urlAPI.urlGetInsuranceRateDetail}/${INSURANCE_ID}`;

                try {
                    const response = await getInsuranceRateDetail(URL);

                    if (response.code === 200) {
                        this.kinds = response.data.kinds;
                        this.name = response.data.name;
                        this.current_rate = response.data.current_rate;
                        this.change_rate = response.data.change_rate;
                        this.applicable_date = response.data.applicable_date;
                    }
                } catch (error) {
                    console.log(error);
                }
            }

            this.overlay.show = false;
        },
        navigateToIndexScreen() {
            this.$router.push({ name: 'InsuranceRateMasterIndex' });
        },
        async onClickSave() {
            this.overlay.show = true;

            const date = new Date(this.applicable_date);

            const formattedDate = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-01`;

            const INSURANCE_ID = this.$route.params.id;

            if (INSURANCE_ID) {
                const DATA = {
                    change_rate: this.change_rate,
                    applicable_date: formattedDate,
                };

                const URL = `${urlAPI.urlPutInsuranceRate}/${INSURANCE_ID}`;

                try {
                    const response = await putInsuranceRate(URL, DATA);

                    console.log(response);

                    if (response.code === 200) {
                        this.$toast.success({ content: '保険を更新しました' });
                        this.$router.push({ name: 'InsuranceRateMasterIndex' });
                    }
                } catch (error) {
                    console.log(error);
                }

                this.overlay.show = false;
            }
        },
        onMonthChanged(newMonth) {
            if (newMonth.hasOwnProperty.call('month')) {
                console.log('SPECIAL CASE');
            } else {
                this.applicable_date = new Date(newMonth.getFullYear(), newMonth.getMonth(), 1);
            }
        },
    },
};
</script>

<style lang="scss" scoped>

::v-deep .year-month-picker {
  width: 100%;
  color: #495057;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  border: 1px solid #ced4da;
  padding: 0.375rem 0.75rem;
  height: calc(1.5em + 0.75rem + 2px);
}

.insurance-rate-master-edit {
  .main-content {
    padding: 60px 100px 0px 100px;

    label {
      font-size: 22px;
      font-weight: 500;
      color: #6C757D;
    }
  }

  .footer {
    padding: 60px 100px 0px 100px;

    .button-back {
      width: 140px;
      color: white;
      background-color: #FF8A1F;
    }

    .button-save {
      float: right;
      width: 140px;
      color: white;
      background-color: #FF8A1F;
    }
  }
}
</style>
