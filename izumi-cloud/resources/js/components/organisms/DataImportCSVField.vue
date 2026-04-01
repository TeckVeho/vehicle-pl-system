<template>
	<div class="data-import-cvs-zone">
		<b-row class="data-import-cvs-zone__header">
			<b-col cols="12">
				<span>{{ $t('DATA_IMPORT.CVS') || 'EXCEL' }}</span>
			</b-col>
		</b-row>

		<div class="data-import-cvs-zone__main-content">
			<b-row class="item" style="padding: 30px 0 30px 0;" lg="2" md="1" sm="2">
				<b-col cols="6" class="label">
					<span>{{ $t('DATA_IMPORT.SELECT_DATA') }}:</span>
				</b-col>

				<b-col cols="6">
					<b-form-select v-model="selected_data" style="width: 250px;" class="btn-select-data">
						<b-form-select-option :value="null" :disabled="true">
							{{ $t('DATA_IMPORT.SELECT_DATA') }}
						</b-form-select-option>

						<b-form-select-option
							v-for="(option, index) in ListData"
							:key="index"
							:value="option.id"
							:disabled="option.is_import === 0"
						>
							{{ option.name }}
						</b-form-select-option>
					</b-form-select>
				</b-col>
			</b-row>

			<b-row class="item" style="padding-bottom: 30px;" lg="2" md="1" sm="1">
				<b-col cols="6" class="label">
					年月選択：
				</b-col>

				<b-col cols="6">
					<div class="custom-year-month-picker d-flex">
						<button
							type="button"
							aria-haspopup="dialog"
							aria-expanded="false"
							style="position: absolute; top: 5px;"
							class="btn h-auto"
						>
							<i class="fas fa-calendar" style="font-size: 24px;" />
						</button>

						<div class="custome-year-month-input" @click="handleYearMonthSelect()">
							<span v-if="yearMonth" class="d-inline-flex" style="position: absolute; left: 80px;">
								{{ yearMonth }}
							</span>
							<span v-else class="d-inline-flex" style="position: absolute; left: 80px;">年月選択</span>
						</div>
					</div>

					<MonthPickerInput
						v-show="false"
						no-default
						:lang="lang"
						style="z-index: 999 !important;"
						@change="handleAssignYearMonth"
					/>
				</b-col>
			</b-row>

			<b-row class="item" style="padding-bottom: 45px;" lg="2" md="1" sm="1">
				<b-col cols="6" class="label">
					<span>{{ $t('DATA_IMPORT.IMPORT_DATA_CSV') }}:</span>
				</b-col>

				<b-col cols="6">
					<input
						id="fileUpload"
						ref="selectFileInput"
						type="file"
						accept=".csv, .xlsx"
						name="File Upload"
						style="display: none;"
						@change="getFileCSVInput"
					>

					<vButton
						style="width: 220px;"
						class="btn-select-file"
						:class-name="'v-button-select-data'"
						:text-button="convertStringToDot(fileNameCSVInput)"
						@click.native="triggerSelectFileInput()"
					/>
				</b-col>
			</b-row>
		</div>
	</div>
</template>

<script>
import vButton from '@/components/atoms/vButton';

import { MonthPickerInput } from 'vue-month-picker';
import { getDataList } from '@/api/modules/dataList';
import { convertStringToDot } from '@/utils/convertStringToDot';

const urlApi = {
    gestDataList: '/data/get-list-data-import',
};

export default {
    name: 'DataImportCSVField',
    components: {
        vButton,
        MonthPickerInput,
    },
    data() {
        return {
            fileNameCSVInput: this.$t('DATA_IMPORT.SELECT_FILES'),
            ListData: [],
            ListDataNew: [],
            selected_data: null,
            yearMonth: '',
            convertStringToDot: convertStringToDot,
        };
    },
    computed: {
        lang() {
            return this.$store.getters.language;
        },
        isFileDataChange() {
            return this.selected_data;
        },
    },
    watch: {
        async isFileDataChange() {
            await this.$bus.emit('idSelectedData', this.selected_data);
        },
    },
    created() {
        document.body.addEventListener('click', (event) => {
            const monthPickerInput = document.getElementsByClassName('month-picker-input')[0];
            const monthPickerDefault = document.getElementsByClassName('month-picker--default')[0];
            const monthPickerInputContainer = document.getElementsByClassName('month-picker-input-container')[0];
            const monthPicker = document.getElementsByClassName('month-picker')[0];

            if (monthPickerInputContainer && monthPicker) {
                if (!monthPickerInputContainer.contains(event.target)) {
                    monthPickerDefault.style.top = '10px';
                    monthPickerInput.style.display = 'none';
                    monthPickerDefault.style.display = 'none';
                    monthPickerInputContainer.style.display = 'none';
                } else if (monthPicker.contains(event.target)) {
                    monthPickerDefault.style.top = '10px';
                    monthPickerInput.style.display = 'none';
                    monthPickerDefault.style.display = 'none';
                    monthPickerInputContainer.style.display = 'none';
                }
            }
        }, true);

        this.getDataSelectList();
    },
    methods: {
        handleAssignYearMonth(ctx) {
            let result = '';

            if (ctx['year'] && ctx['month']) {
                result = `${ctx['year']}-${this.formatMonth(ctx['monthIndex'])}`;
            }

            this.yearMonth = result;

            this.$bus.emit('yearMonth', this.yearMonth);
        },

        formatMonth(month) {
            if (month < 10) {
                return `0${month}`;
            } else {
                return month;
            }
        },

        handleYearMonthSelect() {
            const monthPickerInputContainer = document.getElementsByClassName('month-picker-input-container')[0];

            const monthPickerInput = document.getElementsByClassName('month-picker-input')[0];

            monthPickerInputContainer.style.display = 'block';

            monthPickerInput.style.display = 'none';

            const monthPickerDefault = document.getElementsByClassName('month-picker--default')[0];

            monthPickerDefault.style.display = 'block';
            monthPickerDefault.style.top = '10px';

            const monthPickerYear = document.getElementsByClassName('month-picker__year')[0];

            const buttonOne = monthPickerYear.getElementsByTagName('button')[0];
            buttonOne.setAttribute('style', 'padding-bottom: 52px !important;');
            buttonOne.setAttribute('class', 'previousButton');

            const buttonTwo = monthPickerYear.getElementsByTagName('button')[1];
            buttonTwo.setAttribute('style', 'padding-bottom: 52px !important;');
            buttonOne.setAttribute('class', 'nextButton');

            const p = monthPickerYear.getElementsByTagName('p')[0];
            p.setAttribute('style', 'margin-bottom: 30px !important;');
        },

        async getDataSelectList() {
            this.$bus.emit('overlayDataImport', true);
            await getDataList(urlApi.gestDataList)
                .then((response) => {
                    const RAW_ARRAY = response.data;
                    this.ListData = RAW_ARRAY;
                })
                .catch(() => {
                    this.ListData = [];

                    this.$toast.danger({
                        content: this.$t('TOAST_HAVE_ERROR'),
                    });
                });
            this.$bus.emit('overlayDataImport', false);
        },

        triggerSelectFileInput() {
            this.$refs.selectFileInput.click();
        },

        getFileCSVInput() {
            const fileInput = document.getElementById('fileUpload');
            this.fileNameCSVInput = fileInput.files[0].name;
            this.$bus.emit('nameCSVInput', fileInput.files[0].name);
            this.$bus.emit('fileInput', fileInput.files);
        },
    },
};
</script>

<style lang="scss" scoped >
@import '@/scss/variables.scss';

.custome-year-month-input {
  height: 50px;
  width: 250px;
  display: flex;
  padding-left: 50px;
  border-radius: 4px;
  align-items: center;
  justify-content: center;
  border: 1px solid rgb(206, 212, 218);
}

.data-import-cvs-zone {
  min-height: 300px;
  text-align: center;
  margin: 100px auto;
  border: 2px solid #999999;
  margin-bottom: 50px !important;

  &__header {
    margin: 0;
    padding: 15px;
    min-height: 60px;
    font-weight: bolder;
    text-align: left !important;
    border-bottom: 2px solid #999999;
  }

  &__main-content {
    margin: auto;

    .item {
      margin: 0;

      .label {
        width: 100%;
        display: flex;
        margin: 15px auto;
        justify-content: right;
      }

      .btn-select-data,
      .btn-select-file {
        width: 100%;
        display: flex;
        color: '#FFFFFF' !important;
        justify-content: left;
      }

      button.btn.btn-select-file.btn-secondary.v-button-select-data {
        border: none;
        align-items: center;
        justify-content: center;
        background-color: $picton-blue;
      }
    }

    .btn-select-data {
      width: 250px;
      height: 50px;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }
  }
}
</style>
