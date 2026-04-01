<template>
	<b-overlay
		:show="overlay.show"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
		:blur="overlay.blur"
		:rounded="overlay.sm"
	>
		<!-- Template overlay -->
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p class="text-loading">{{ $t('PLEASE_WAIT') }}</p>
			</div>
		</template>

		<div class="employee-master-list">
			<vHeaderPage class="employee-master-list__title-header">
				{{ $t('PAGE_TITLE.EMPLOYEE_MASTER') }}
			</vHeaderPage>

			<div class="employee-master-list employee-master-list__content">

				<!-- Tabs -->
				<div class="employee-master-list__tabs">
					<div
						v-for="(tab, index) in tabs"
						:key="index"
						class="employee-master-list__tab"
						:class="{ active: activeTab === index }"
						@click="activeTab = index"
					>
						{{ tab.label }}
					</div>
				</div>

				<!-- Content -->
				<div class="employee-master-list__content">
					<component
						:is="tabs[activeTab].component"
						:data="currentTabData"
						:key="tabs[activeTab].id"
						@update-success="handleRefreshData"
					/>
				</div>

			</div>
		</div>
	</b-overlay>
</template>

<script>

import vHeaderPage from '@/components/atoms/vHeaderPage';
// import WorkerBasicInfo from '@/pages/EmployeeMaster/tabs/WorkerBasicInfo.vue';
import DriversLicense from '@/pages/EmployeeMaster/tabs/DriversLicense.vue';
import DrivingRecord from '@/pages/EmployeeMaster/tabs/DrivingRecord.vue';
import AptitudeTest from '@/pages/EmployeeMaster/tabs/AptitudeTest.vue';
import HealthExam from '@/pages/EmployeeMaster/tabs/HealthExam.vue';
import DetailEmployeeMaster from '@/pages/EmployeeMaster/tabs/DetailEmployee.vue';
import 'vue2-datepicker/index.css';
import { getDetailEmployee } from '@/api/modules/employeeMaster';

const urlAPIs = {
    apiGetEmployeeDetail: '/employee',
};
export default {
    name: 'EmployeeMaster',
    components: {
        vHeaderPage,
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

            activeTab: 0,
            tabs: [
                { id: 1, label: '従業員マスタ', component: DetailEmployeeMaster, data: { }},
                // { id: 2, label: '労働者名簿', component: WorkerBasicInfo, data: { }},
                { id: 2, label: '運転免許証', component: DriversLicense, data: { }},
                { id: 3, label: '運転記録証明書', component: DrivingRecord, data: { }},
                { id: 4, label: '適性診断票', component: AptitudeTest, data: { }},
                { id: 5, label: '健康診断結果通知書', component: HealthExam, data: { }},
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
                employee_role: null,
                dataTable: [],
                first_time: '',
                aligible_age: '',
                special: '',
                general: '',
            },

            employeeRaw: null,

        };
    },
    computed: {

        lang() {
            return this.$store.getters.language;
        },

        currentTabData() {
            // const tabId = this.tabs[this.activeTab].id;
            // if (tabId === 2) {
            //     return this.employee;
            // }
            return this.employeeRaw || {};
        },
    },
    watch: {

    },
    created() {
        this.getEmployeeDetailData();
    },

    methods: {
        async getEmployeeDetailData() {
            try {
                const URL = `${urlAPIs.apiGetEmployeeDetail}/${this.$route.params.id}`;

                const response = await getDetailEmployee(URL);

                if (response.code === 200) {
                    const EMPLOYEE_DATA = response.data.employee;
                    this.employeeRaw = EMPLOYEE_DATA;

                    this.employee.id = EMPLOYEE_DATA.employee_code;
                    this.employee.name = this.handleTransformName(EMPLOYEE_DATA.name);

                    this.employee.name_phonetic = EMPLOYEE_DATA.name_in_furigana;
                    this.employee.date_of_appointment = EMPLOYEE_DATA.date_of_election;
                    this.employee.address = EMPLOYEE_DATA.address;
                    this.employee.contact_phone_number = EMPLOYEE_DATA?.user_contacts.personal_tel;
                    this.employee.previous_employment_history = EMPLOYEE_DATA.previous_employment_history;
                    this.employee.aptitude_test_date = EMPLOYEE_DATA.aptitude_assessment_forms.length > 0 ? EMPLOYEE_DATA.aptitude_assessment_forms[0]?.date_of_visit : '';
                    this.employee.medical_examination_date = EMPLOYEE_DATA.health_examination_results.length > 0 ? EMPLOYEE_DATA.health_examination_results[0]?.date_of_visit : '';
                    this.employee.age_appropriate_interview = EMPLOYEE_DATA.age_appropriate_interview;
                    this.employee.selected_classroom = EMPLOYEE_DATA.beginner_driver_training_classroom;
                    this.employee.selected_practical = EMPLOYEE_DATA.beginner_driver_training_practical;

                    this.employee.first_time = EMPLOYEE_DATA.aptitude_assessment_forms.length > 0 ? (EMPLOYEE_DATA.aptitude_assessment_forms[0]?.file_history.find(item => item.type === 1))?.date_of_visit : '';
                    this.employee.aligible_age = EMPLOYEE_DATA.aptitude_assessment_forms.length > 0 ? (EMPLOYEE_DATA.aptitude_assessment_forms[0]?.file_history.find(item => item.type === 2))?.date_of_visit : '';
                    this.employee.special = EMPLOYEE_DATA.aptitude_assessment_forms.length > 0 ? (EMPLOYEE_DATA.aptitude_assessment_forms[0]?.file_history.find(item => item.type === 3))?.date_of_visit : '';
                    this.employee.general = EMPLOYEE_DATA.aptitude_assessment_forms.length > 0 ? (EMPLOYEE_DATA.aptitude_assessment_forms[0]?.file_history.find(item => item.type === 4))?.date_of_visit : '';

                    this.employee.email = EMPLOYEE_DATA.email;
                    this.employee.gender = this.handleTransformSex(EMPLOYEE_DATA.sex);
                    this.employee.birthday = EMPLOYEE_DATA.birthday;
                    this.employee.workingType = this.handleTransformWorkingType(EMPLOYEE_DATA.job_type);
                    this.employee.employeeType = this.handleTransformEmployeeType(EMPLOYEE_DATA.employee_type);
                    this.employee.licenseType = this.handleTransformLicenseType(EMPLOYEE_DATA.license_type);
                    this.employee.hireStartDate = EMPLOYEE_DATA.hire_start_date;
                    this.employee.retirementDate = EMPLOYEE_DATA.retirement_date;
                    this.employee.welfareExpense = EMPLOYEE_DATA.welfare_expense;
                    this.employee.employee_role = EMPLOYEE_DATA.employee_role;
                    this.employee.dataTable = EMPLOYEE_DATA.driver_licenses;

                    console.log('taaaaaa@@@@a');
                    this.tabs.map((tab) => {
                        tab.data = EMPLOYEE_DATA;
                    });
                }
            } catch (error) {
                console.log('[ERROR]', error);
            }
        },

        handleTransformName(string) {
            if (string.length > 0) {
                return string.replaceAll('/', '');
            } else {
                return '';
            }
        },

        handleTransformSex(gender) {
            if (gender === 0) {
                return this.$t('MALE');
            } else if (gender === 1) {
                return this.$t('FEMALE');
            } else {
                return '[Error Trasnform Sex]';
            }
        },

        handleTransformWorkingType(job_type) {
            if (job_type === 0) {
                return this.$t('DRIVER');
            } else if (job_type === 1) {
                return this.$t('DESKWORKER');
            } else if (job_type === 2) {
                return this.$t('OPERATOR');
            } else {
                return '[Error Transform Working Type]';
            }
        },

        handleTransformEmployeeType(employee_type) {
            if (employee_type === 0) {
                return this.$t('FULL_TIME');
            } else if (employee_type === 1) {
                return this.$t('PART_TIME');
            } else if (employee_type === 3) {
                return this.$t('TEMPORARY_STAFF');
            } else {
                return '[Error Transform Employee Type]';
            }
        },

        handleTransformLicenseType(license_type) {
            if (license_type === 0) {
                return this.$t('ORDINARY');
            } else if (license_type === 1) {
                return this.$t('SEMI_MEDIUM_5T');
            } else if (license_type === 2) {
                return this.$t('SEMI_MEDIUM');
            } else if (license_type === 3) {
                return this.$t('MEDIUM_8T');
            } else if (license_type === 4) {
                return this.$t('MEDIUM');
            } else if (license_type === 5) {
                return this.$t('LARGE');
            } else if (license_type === 6) {
                return this.$t('TRACTION');
            } else {
                return '[Error Transform License Type]';
            }
        },

        handleRefreshData() {
            this.getEmployeeDetailData();
        },
    },
};
</script>

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

.employee-master-list__content {
    width: 100%;
    // margin-top: 20px;
}

.employee-master-list {
  background: #fff;
}

/* Tabs container */
.employee-master-list__tabs {
  display: flex;
  border-bottom: 1px solid #ddd;
}

/* Each tab */
.employee-master-list__tab {
    text-align: center;
  width: 20%;
  padding: 16px 32px;
  cursor: pointer;
  font-weight: 600;
  color: #333;
  background: #fff;
  border: 1px solid transparent;
  border-bottom: none;
}

/* Active tab */
.employee-master-list__tab.active {
  border: 1px solid #c24b3a;
  border-bottom: 4px solid #c24b3a;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  position: relative;
  top: 1px;
}

/* Content */
.employee-master-list__content {
  padding: 32px;
  background: #fafafa;
  min-height: 300px;
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

::v-deep .darker-bg-td {
  color: #FFFFFF !important;
  background-color: #000000 !important;
}

::-webkit-scrollbar {
  height: 3px;
}

::-webkit-scrollbar-thumb {
  border-radius: 45px;
}

.text-loading {
    margin-top: 10px;
}

</style>
