<template>
	<div class="worker-basic-info">
		<h5 class="title">{{ $t('EMPLOYEE_MASTER_DETAIL_TITLE_BASIC_DATA') }}</h5>
		<hr class="hr_class">

		<div class="employee-master-detail">
			<div class="employee-master-detail__basic-data">

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="employee-id">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_EMPLOYEE_ID') }}
							</label>
							<b-form-input id="employee-id" v-model="employee.id" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="employee-name">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_EMPLOYEE_NAME') }}
							</label>
							<b-form-input id="employee-name" v-model="employee.name" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="name-phonetic">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_NAME_PHONETIC') }}
							</label>
							<b-form-input id="name-phonetic" v-model="employee.name_phonetic" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="gender">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_GENDER') }}
							</label>
							<b-form-input id="gender" v-model="employee.gender" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="birthday">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_BIRTHDAY') }}
							</label>
							<b-form-input id="birthday" v-model="employee.birthday" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="hire-start-date">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_HIRE_START_DATE') }}
							</label>
							<b-form-input id="hire-start-date" v-model="employee.hireStartDate" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="date_of_appointment">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_DATE_OF_APPOINTMENT') }}
							</label>
							<b-form-input id="date_of_appointment" v-model="employee.date_of_appointment" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="address">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_ADDRESS') }}
							</label>
							<b-form-input id="address" v-model="employee.address" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="contact-phone-number">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_CONTEACT_PHONE_NUMBER') }}
							</label>
							<b-form-input id="contact-phone-number" v-model="employee.contact_phone_number" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="working-type">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_WORKING_TYPE') }}
							</label>
							<b-form-input id="working-type" v-model="employee.workingType" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="employee-type">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_EMPLOYEE_TYPE') }}
							</label>
							<b-form-input id="employee-type" v-model="employee.employeeType" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="license-type">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_LICENSE_TYPE') }}
							</label>
							<b-form-input id="license-type" v-model="employee.licenseType" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="retirement-date">
								{{ $t('EMPLOYEE_MASTER_DETAIL_LABEL_RETIREMENT_DATE') }}
							</label>
							<b-form-input id="retirement-date" v-model="employee.retirementDate" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data title_new-driver-training">
							<label for="contact-phone-number">
								{{ $t('EMPLAYEE_MASTER_DETAIL_DRIVER_LICENSE_INFORMATION') }}
							</label>
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="previous-employment-history">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_PREVIOUS_EMPLOYMENT_HISTORY') }}
							</label>
							<b-form-input id="previous-employment-history" v-model="employee.previous_employment_history" disabled />
						</div>
					</b-col>
					<!-- <b-col>
						<div class="item-data">
						<label for="aptitude-test-date">
						{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_APTITUDE_TEST_DATE') }}
						</label>
						<b-form-input id="aptitude-test-date" v-model="employee.aptitude_test_date" disabled />
						</div>
						</b-col> -->
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data title_new-driver-training">
							<label for="contact-phone-number font-weight-bold">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_APTITUDE_TEST_DATE') }}
							</label>
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="previous-employment-history">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_FIRST_TIMER') }}
							</label>
							<b-form-input id="previous-employment-history" v-model="employee.first_time" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="aptitude-test-date">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_ELIGIBLE_AGE') }}
							</label>
							<b-form-input id="aptitude-test-date" v-model="employee.aligible_age" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="previous-employment-history">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_SPECIAL') }}
							</label>
							<b-form-input id="previous-employment-history" v-model="employee.special" disabled />
						</div>
					</b-col>
					<b-col>
						<div class="item-data">
							<label for="aptitude-test-date">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_GENERAL') }}
							</label>
							<b-form-input id="aptitude-test-date" v-model="employee.general" disabled />
						</div>
					</b-col>
				</b-row>

				<b-row>
					<b-col>
						<div class="item-data">
							<label for="medical-examination-date">
								{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_MEDICAL_EXAMINATION_DATE') }}
							</label>
							<b-form-input id="medical-examination-date" v-model="employee.medical_examination_date" disabled />
						</div>
					</b-col>
				</b-row>
			</div>
		</div>

		<!-- <h5 class="title">{{ $t('NEW_DRIVER_TRAINING') }}</h5>
			<hr class="hr_class"> -->

		<div class="employee-master-detail__basic-data">
			<b-row>
				<b-col>
					<div class="item-data">
						<p>{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_NEWDRIVER_TRANING_CLASSROOM') }}</p>
						<b-form-radio-group
							v-model="employee.selected_classroom"
							:options="options"
							class="mb-3"
							value-field="item"
							text-field="name"
							disabled-field="notEnabled"
						/>
					</div>

					<div class="item-data">
						<p>{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_NEWDRIVER_TRANING_PRACTICAL') }}</p>
						<b-form-radio-group
							v-model="employee.selected_practical"
							:options="options_practical"
							class="mb-3"
							value-field="item"
							text-field="name"
							disabled-field="notEnabled"
						/>
					</div>

				</b-col>
			</b-row>
			<b-row>
				<b-col>
					<div class="section">
						<p>{{ $t('EMPLAYEE_MASTER_DETAIL_LABEL_AGE_APPROPRIATE_INTERVIEW') }}</p>
						<b-form-radio-group
							v-model="employee.age_appropriate_interview"
							:options="options"
							class="mb-3"
							value-field="item"
							text-field="name"
							disabled-field="notEnabled"
						/>
					</div>

					<!-- <div class="section">
						<p>{{ $t('NEW_DRIVER_TRAINING_PRACTICAL') }}</p>
						<b-form-radio-group
						v-model="employee.selected_practical"
						:options="options_practical"
						class="mb-3"
						value-field="item"
						text-field="name"
						disabled-field="notEnabled"
						/>
						</div> -->

				</b-col>
			</b-row>

			<b-col>
				<div class="btn-return-footer mt-3">
					<vButton
						:class="'btn-return'"
						@click.native="onClickReturn()"
					>
						{{ $t('BUTTON.BACK') }}
					</vButton>
				</div>
			</b-col>

		</div>
	</div>
</template>

<script>

import vButton from '@/components/atoms/vButton';

export default {
    name: 'WorkerBasicInfo',
    components: {
        vButton,
    },
    props: {
        data: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            options: [
                { name: this.$t('COMPLETED'), item: '1', notEnabled: true },
                { name: this.$t('NOT_COMPLETED'), item: '2', notEnabled: true },
            ],
            options_practical: [
                { name: this.$t('COMPLETED'), item: '1', notEnabled: true },
                { name: this.$t('NOT_COMPLETED'), item: '2', notEnabled: true },
            ],
            employee: {
                id: '',
                name: '',
                name_phonetic: '',
                date_of_appointment: '',
                address: '',
                contact_phone_number: '',
                previous_employment_history: '',
                aptitude_test_date: '',
                medical_examination_date: '',
                age_appropriate_interview: null,
                selected_classroom: null,
                selected_practical: null,
                email: '',
                gender: '',
                birthday: '',
                workingType: '',
                licenseType: '',
                employeeType: '',
                employeeRole: '',
                hireStartDate: '',
                retirementDate: '',
                welfareExpense: '',
                first_time: '',
                aligible_age: '',
                special: '',
                general: '',
            },

            employee_role_options: [
                { value: null, text: '選択してください' },
                { value: 1, text: '部長' },
                { value: 2, text: '本部長' },
                { value: 3, text: '常務' },
                { value: 4, text: '社長' },
            ],

            equipmentData: {
                company_car: '',
                etc_card: '',
                fuel_card: '',
                other: '',
                employee_role: null,
            },
        };
    },
    computed: {

        lang() {
            return this.$store.getters.language;
        },
    },
    watch: {

    },
    created() {
        console.log('data in workerBasicInfo:', this.data);
        this.employee = this.data;
    },

    methods: {

        onClickReturn() {
            this.$router.push({ name: 'EmployeeMaster' }).catch(() => {});
        },

    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables';
.title {
    font-size: 30px;
    font-weight: 600;
    margin-bottom: 16px;
}
.hr_class {
    border: 1px solid black;
    margin-bottom: 24px;
}
.check_box_classroom {
    display: flex;
    gap: 20px;
}

.employee-master-detail {
  overflow: hidden;
  min-height: calc(100vh - 89px);

  &__basic-data {
    margin-bottom: 20px;
  }

  &__basic-data {

    .item-data {
      margin-bottom: 10px;

      label {
        font-weight: 500;
      }

      span.text-link {
        font-weight: 500;
        text-decoration: underline;
        cursor: pointer;
      }

      .zone-node {
        margin-bottom: 25px;
      }

      .history-button {
        min-width: 150px;
        color: #FFFFFF;
        background-color: #FF8A1F;
      }

    }
	.title_new-driver-training {
        font-weight: 600;
		margin-top: 20px;
	}

    .title_font {
        font-weight: 600;
    }
  }
}
</style>
