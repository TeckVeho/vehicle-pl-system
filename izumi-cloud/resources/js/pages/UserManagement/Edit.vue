<template>
	<div>
		<b-overlay :show="overlay.show" :variant="overlay.variant" :opacity="overlay.opacity" :blur="overlay.blur" :rounded="overlay.sm">
			<template #overlay>
				<div class="text-center">
					<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
					<p style="margin-top: 10px;">{{ $t('PLEASE_WAIT') }}</p>
				</div>
			</template>

			<b-col>
				<div class="content-body">
					<b-row>
						<b-col class="input-row" cols="12">
							<vLabel :class-name="'user-regis-label'" :text-label="$t('USER_MANAGEMENT.USER_ROLE')" />
							<vSelect v-model="user_role" :data-options="options" :disabled="isProcess" :value="user_role" :class-name="'user-regis-select'" :placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_USER_ROLE')" />
							<template v-if="user_role === 1">
								<b-form-checkbox v-model="assign_vehicle_personnel" size="lg" class="mt-3" value="true" unchecked-value="false">
									<span>車両担当者権限を付与する</span>
								</b-form-checkbox>
							</template>
						</b-col>

						<b-col class="input-row" cols="12">
							<vLabel :class-name="'user-regis-label'" :text-label="$t('USER_MANAGEMENT.EMAIL')" />
							<vInput v-model="email" :class-name="'user-regis-input user-full-name'" :placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_EMAIL')" :disabled="isProcess" />
						</b-col>

						<b-col class="input-row" cols="12">
							<span :class="{'text-link': dataSendMail.email !== null, 'text-link-disabled': dataSendMail.email === null}" @click="dataSendMail.email !== null ? handleSendMailSetUpPassword() : null">
								パスワード初期設定メールを送信
							</span>
						</b-col>

						<b-col class="input-row" cols="12">
							<div class="mail-history-title">送信履歴</div>
							<div class="mail-history-table-wrapper">
								<b-table-simple id="password-mail-history-table" hover bordered class="mail-history-table">
									<b-thead>
										<b-tr>
											<b-th class="text-center">{{ $t('DATE_TIME') }}</b-th>
											<b-th class="text-center">{{ $t('REGISTERED_BY') }}</b-th>
										</b-tr>
									</b-thead>

									<b-tbody v-if="passwordSetupMailHistories.length > 0">
										<b-tr v-for="(history, index) in passwordSetupMailHistories" :key="index">
											<b-td class="text-center">{{ history.sent_at }}</b-td>
											<b-td class="text-center">{{ history.sender_name }}</b-td>
										</b-tr>
									</b-tbody>

									<b-tbody v-else>
										<b-tr>
											<b-td class="text-center" colspan="2" rowspan="2">送信メール履歴データがありません</b-td>
										</b-tr>
										<b-tr />
									</b-tbody>
								</b-table-simple>
							</div>
						</b-col>

						<b-col class="input-row" cols="12">
							<vLabel :class-name="'user-regis-label'" :text-label="$t('USER_MANAGEMENT.EMPLOYEE_NAME')" />
							<vInput v-model="employee_name" :class-name="'user-regis-input user-full-name'" :placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_EMPLOYEE_NAME')" :disabled="true" />
						</b-col>

						<b-col class="input-row" cols="12">
							<vLabel :class-name="'user-regis-label'" :text-label="$t('USER_MANAGEMENT.USER_ID')" />
							<vInput :value="user_id" :type="'number'" :class-name="'user-regis-input user_id'" :placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_USER_ID')" :disabled="true" />
						</b-col>

						<b-col class="input-row" cols="12">
							<vLabel :class-name="'user-regis-label'" :text-label="$t('USER_MANAGEMENT.PASSWORD')" />
							<vInput v-model="password" :type="'password'" :class-name="'user-regis-input user_password'" :placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_PASSWORD')" :disabled="true" :autocomplete="'new-password'" />
						</b-col>

						<b-col class="input-row" cols="12">
							<vLabel :class-name="'user-regis-label'" :text-label="$t('USER_MANAGEMENT.NEW_PASSWORD')" />
							<vInput v-model="new_password" :type="'password'" :class-name="'user-regis-input user_new_password'" :placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_NEW_PASSWORD')" :disabled="true" :autocomplete="'new-password'" />
						</b-col>

						<b-col class="input-row" cols="12">
							<b-row class="footer-functional-buttons" lg="2" md="1" sm="1">
								<b-col>
									<vButton id="btn-back" :text-button="$t('BUTTON.BACK')" :class-name="'v-button-default'" :disabled="isProcess" @click.native="backToUserList()" />
								</b-col>

								<b-col>
									<vButton id="btn-save" :text-button="$t('BUTTON.SAVE')" :class-name="'v-button-default'" :disabled="isProcess" @click.native="doEdit()" />
								</b-col>
							</b-row>
						</b-col>
					</b-row>
				</div>
			</b-col>
		</b-overlay>
	</div>
</template>

<script>
import { getOneUser, getRoleList, updateUser, sendMailSetUpPassword } from '@/api/modules/user';

const API_URLS = {
    urlGetOneUser: `/user/`,
    urlGetListRole: `/role`,
    urlUpdateUser: `/user/`,
    urlSendMailSetUpPassword: `/send-mail-set-up-password`,
};

import vLabel from '@/components/atoms/vLabel';
import vInput from '@/components/atoms/vInput';
import vSelect from '@/components/atoms/vSelect';
import vButton from '@/components/atoms/vButton';

import { validEmptyOrWhiteSpace, validateNumberMoreThanZero, validPassword } from '@/utils/validate';

export default {
    name: 'UserManagementEdit',
    components: {
        vLabel,
        vInput,
        vButton,
        vSelect,
    },
    data() {
        return {
            isProcess: false,
            user_role: null,
            employee_name: '',
            user_id: this.$route.params.id,
            password: '',
            new_password: '',
            email: '',
            assign_vehicle_personnel: false,
            options: [
                { value: null, text: this.$t('PLEASE_SELECT'), disabled: true },
            ],
            isPassValidation: false,
            DataUser: {
                role: '',
                name: '',
                id: '',
                current_password: '',
                password: '',
                email: '',
                assign_vehicle_personnel: false,
            },
            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },
            dataSendMail: {
                user_code: '',
                email: '',
            },
            passwordSetupMailHistories: [],
        };
    },
    created() {
        this.handleInitData();
    },
    methods: {
        async handleInitData() {
            await this.getListRole();
            await this.getOneUserData();
        },
        async getOneUserData() {
            this.overlay.show = true;
            try {
                await getOneUser(API_URLS.urlGetOneUser + this.user_id).then((response) => {
                    this.user_role = response.data.role;
                    this.employee_name = response.data.name;
                    this.user_id = response.data.id + '';
                    this.email = response.data.email;
                    this.assign_vehicle_personnel = response.data.assign_vehicle_personnel === 1 ? 'true' : 'false';
                    this.dataSendMail = {
                        user_code: response.data.id,
                        email: response.data.email,
                    };
                    this.passwordSetupMailHistories = this.mapPasswordSetupMailHistories(response.data.user_password_history);
                });
            } finally {
                this.overlay.show = false;
            }
        },
        backToUserList() {
            this.$router.push('/master-manager/user-master');
        },
        validation() {
            this.DataUser = {
                role: this.user_role,
                name: this.employee_name,
                id: this.user_id,
                email: this.email,
                assign_vehicle_personnel: this.assign_vehicle_personnel === 'true' ? 1 : 0,
            };

            if (this.user_role !== 1) {
                this.DataUser.assign_vehicle_personnel = 0;
            }

            if (!validateNumberMoreThanZero(this.user_role)) {
                this.$toast.warning({
                    content: this.$t('REQUIRE_USER_ROLE'),
                });
            } else if (validEmptyOrWhiteSpace(this.employee_name)) {
                this.$toast.warning({
                    content: this.$t('REQUIRE_EMPLOYEE_NAME'),
                });
            } else if (validEmptyOrWhiteSpace(this.user_id)) {
                this.$toast.warning({
                    content: this.$t('REQUIRE_USER_ID'),
                });
            } else {
                if (this.password) {
                    if (!validPassword(this.password)) {
                        this.$toast.warning({
                            content: this.$t('REQUIRE_PASSWORD_WITH_VALIDATE'),
                        });
                        return;
                    } else {
                        if (!validPassword(this.new_password)) {
                            this.$toast.warning({
                                content: this.$t('REQUIRE_PASSWORD_NEW_WITH_VALIDATE'),
                            });
                            return;
                        } else {
                            this.DataUser.current_password = this.password;
                            this.DataUser.password = this.new_password;
                        }
                    }
                }

                this.isPassValidation = true;
            }
        },
        async doEdit() {
            this.isProcess = true;

            this.validation();

            if (this.isPassValidation === true) {
                updateUser(API_URLS.urlUpdateUser + this.user_id, this.DataUser)
                    .then((response) => {
                        if (response.code === 200) {
                            this.$toast.success({
                                content: this.$t('USER_MANAGEMENT.TOAST_EDIT_SUCCESS'),
                            });

                            if (response.data.id === this.$store.getters.userId) {
                                this.$store.dispatch('user/doLogout')
                                    .then(() => {
                                        this.$router.push('/login');
                                    })
                                    .catch(() => {
                                        this.$toast.danger({
                                            content: this.$t('TOAST_HAVE_ERROR'),
                                        });
                                    });
                            } else {
                                this.$router.push('/master-manager/user-master');
                            }

                            this.isPassValidation = false;
                        } else {
                            if (response.code === 401) {
                                if (response.message === 'server.current_pass_incorrect') {
                                    this.$toast.warning({
                                        content: this.$t('USER_MANAGEMENT.TOAST_CURRENT_PASS_IN_CORRECT'),
                                    });
                                } else {
                                    this.$toast.danger({
                                        content: this.$t('TOAST_HAVE_ERROR'),
                                    });
                                }
                            } else if ([422].includes(response.code)) {
                                this.$toast.warning({
                                    content: response.message,
                                });
                            } else {
                                console.log(response);
                            }
                        }
                    });
            }

            this.isProcess = false;
        },
        async getListRole() {
            const response = await getRoleList(API_URLS.urlGetListRole);

            let TEMP = [];

            if (response.code === 200) {
                for (let i = 0; i < response.data.length; i++) {
                    TEMP.push({
                        value: response.data[i].id,
                        text: this.$t(this.toI18nKey(response.data[i].name)),
                        disabled: false,
                    });
                }
            }

            // Custom display order: department_office_staff (id 17) appears after TL (id 3)
            const ROLE_DISPLAY_ORDER = {
                1: 1, // crew
                2: 2, // clerks
                3: 3, // tl
                17: 4, // department_office_staff
                6: 5, // personnel_labor
                5: 6, // general_affair
                4: 7, // accounting
                11: 8, // quality_control
                12: 9, // sales
                13: 10, // site_manager
                14: 11, // hq_manager
                15: 12, // department_manager
                16: 13, // executive_officer
                8: 14, // director
                9: 15, // dx_user
                10: 16, // dx_manager
            };

            TEMP = TEMP.sort((a, b) => {
                const orderA = ROLE_DISPLAY_ORDER[a.value] !== undefined ? ROLE_DISPLAY_ORDER[a.value] : 999;
                const orderB = ROLE_DISPLAY_ORDER[b.value] !== undefined ? ROLE_DISPLAY_ORDER[b.value] : 999;
                return orderA - orderB;
            });

            this.options.push(...TEMP);
        },
        toI18nKey(string) {
            if (string) {
                switch (string) {
                case 'crew':
                    return 'USER_MANAGEMENT.ROLE.CREW';
                case 'clerks':
                    return 'USER_MANAGEMENT.ROLE.CLERKS';
                case 'tl':
                    return 'USER_MANAGEMENT.ROLE.TL';
                case 'department_office_staff':
                    return 'USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF';
                case 'accounting':
                    return 'USER_MANAGEMENT.ROLE.ACCOUNTING';
                case 'general_affair':
                    return 'USER_MANAGEMENT.ROLE.GENERAL_AFFAIR';
                case 'personnel_labor':
                    return 'USER_MANAGEMENT.ROLE.PERSONNEL_LABOR';
                case 'headquarter':
                    return 'USER_MANAGEMENT.ROLE.HEADQUARTER';
                case 'am_sm':
                    return 'USER_MANAGEMENT.ROLE.AM_SM';
                case 'quality_control':
                    return 'USER_MANAGEMENT.ROLE.QUALITY_CONTROL';
                case 'sales':
                    return 'USER_MANAGEMENT.ROLE.SALES';
                case 'site_manager':
                    return 'USER_MANAGEMENT.ROLE.SITE_MANAGER';
                case 'hq_manager':
                    return 'USER_MANAGEMENT.ROLE.HQ_MANAGER';
                case 'executive_officer':
                    return 'USER_MANAGEMENT.ROLE.EXECUTIVE_OFFICER';
                case 'department_manager':
                    return 'USER_MANAGEMENT.ROLE.DEPARTMENT_MANAGER';
                case 'director':
                    return 'USER_MANAGEMENT.ROLE.DIRECTOR';
                case 'accountant_direction':
                    return 'USER_MANAGEMENT.ROLE.ACCOUNTANT_DIRECTION';
                case 'dx_user':
                    return 'USER_MANAGEMENT.ROLE.DX_USER';
                case 'dx_manager':
                    return 'USER_MANAGEMENT.ROLE.DX_MANAGER';
                default:
                    return `[${string}]`;
                }
            }
        },
        async handleSendMailSetUpPassword() {
            this.overlay.show = true;
            sendMailSetUpPassword(API_URLS.urlSendMailSetUpPassword, this.dataSendMail)
                .then((response) => {
                    if (response.code === 200) {
                        this.$toast.success({
                            content: this.$t(response.data.message),
                        });
                        this.getOneUserData();
                    } else if (response.code === 422) {
                        this.$toast.danger({
                            content: this.$t(response.data.message),
                        });
                        this.overlay.show = false;
                    }
                })
                .catch(() => {
                    this.$toast.danger({
                        content: this.$t('TOAST_HAVE_ERROR'),
                    });
                    this.overlay.show = false;
                });
        },
        mapPasswordSetupMailHistories(histories) {
            if (!Array.isArray(histories)) {
                return [];
            }

            return histories.map((item) => {
                const rawDateTime = item.send_at || item.sent_at || item.created_at || '';
                return {
                    sent_at: this.formatJapaneseDateTime(rawDateTime),
                    sender_name: (item.user_created_by && item.user_created_by.name) || item.sender_name || (item.user && item.user.name) || (item.sender && item.sender.name) || '',
                };
            });
        },
        formatJapaneseDateTime(value) {
            if (!value) {
                return '';
            }

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hour = String(date.getHours()).padStart(2, '0');
            const minute = String(date.getMinutes()).padStart(2, '0');

            return `${year}年${month}月${day}日 ${hour}時間${minute}分`;
        },
    },
};
</script>

<style lang="scss" scoped>
@import "@/scss/variables.scss";

.content-body {
	min-height: calc(100vh - 89px);

	margin: 0 5px;
	margin: auto;
	max-width: 60%;
	padding-top: 60px;
	padding-bottom: 60px;

	.input-row {
		padding-top: 20px;

		.footer-functional-buttons {
			margin-top: 20px;
			text-align: center;

			button {
				max-width: 150px;
				margin-top: 50px;
			}
		}
	}

	.text-link {
		font-weight: 500;
		text-decoration: underline;
		cursor: pointer;
	}

	.mail-history-title {
		font-weight: 600;
		margin-bottom: 10px;
	}

	.mail-history-table-wrapper {
		max-height: 350px;
		overflow-y: auto;

        &::-webkit-scrollbar {
            width: 4px;
        }

        &::-webkit-scrollbar-thumb {
            background-color: #0f0448 !important;
            border-radius: 8px;
        }

		table {
			border-collapse: separate;
			border-spacing: 0;

            th {
				position: sticky;
				top: 0;
				z-index: 2;
            }
		}
	}

	::v-deep #password-mail-history-table {
		thead {
			th {
				background-color: #0f0448;
				color: #fff;
			}
		}

		tbody {
			td {
				text-align: center;
				vertical-align: middle;
			}
		}
	}
}
</style>
