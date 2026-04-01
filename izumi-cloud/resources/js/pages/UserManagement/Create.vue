<template>
	<div>
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
					<p style="margin-top: 10px;">{{ $t('PLEASE_WAIT') }}</p>
				</div>
			</template>
			<b-col>
				<div class="content-body">
					<b-row>
						<b-col class="input-row" cols="12">
							<vLabel
								:class-name="'user-regis-label'"
								class="user-regis-label"
								:text-label="$t('USER_MANAGEMENT.USER_ROLE')"
							/>
							<vSelect
								v-model="user_role"
								class="user-regis-select"
								:data-options="options"
								:disabled="isProcess"
								:placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_USER_ROLE')"
							/>
							<template v-if="user_role === 1">
								<b-form-checkbox v-model="assign_vehicle_personnel" size="lg" class="mt-3" value="true" unchecked-value="false">
									<span>車両担当者権限を付与する</span>
								</b-form-checkbox>
							</template>
						</b-col>
						<b-col class="input-row" cols="12">
							<vLabel
								:class-name="'user-regis-label'"
								class="user-regis-label"
								:text-label="$t('USER_MANAGEMENT.EMPLOYEE_NAME')"
							/>
							<vInput
								v-model="employee_name"
								:class-name="'user-regis-input user-full-name'"
								:placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_EMPLOYEE_NAME')"
								:disabled="isProcess"
							/>
						</b-col>
						<b-col class="input-row" cols="12">
							<vLabel
								:class-name="'user-regis-label'"
								class="user-regis-label"
								:text-label="$t('USER_MANAGEMENT.USER_ID')"
							/>
							<vInput
								v-model="user_id"
								:type="'number'"
								:class-name="'user-regis-input user_id'"
								:placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_USER_ID')"
								:disabled="isProcess"
							/>
						</b-col>
						<b-col class="input-row" cols="12">
							<vLabel
								:class-name="'user-regis-label'"
								class="user-regis-label"
								:text-label="$t('USER_MANAGEMENT.PASSWORD')"
							/>
							<vInput
								v-model="password"
								:type="'password'"
								:class-name="'user-regis-input user_password'"
								:placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_PASSWORD')"
								:disabled="isProcess"
								:autocomplete="'new-password'"
							/>
						</b-col>
						<b-col class="input-row" cols="12">
							<b-row class="footer-functional-buttons" lg="2" md="1" sm="1">
								<b-col>
									<vButton
										id="btn-back"
										:text-button="$t('BUTTON.BACK')"
										:class-name="'v-button-default'"
										:disabled="isProcess"
										@click.native="backToUserList()"
									/>
								</b-col>
								<b-col>
									<vButton
										id="btn-save"
										:text-button="$t('BUTTON.SIGN_UP')"
										:class-name="'v-button-default btn-save'"
										:disabled="isProcess"
										@click.native="doSignUp()"
									/>
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
// Apis import
import { createUser, getRoleList } from '@/api/modules/user';

const API_URLS = {
    urlcreateUser: `/user`,
    urlGetListRole: `/role`,
};

// Atomic components import
import vLabel from '@/components/atoms/vLabel';
import vInput from '@/components/atoms/vInput';
import vSelect from '@/components/atoms/vSelect';
import vButton from '@/components/atoms/vButton';

// Helper functions import
import { validEmptyOrWhiteSpace, validateNumberMoreThanZero, validPassword } from '@/utils/validate';

export default {
    name: 'UserManagementCreate',
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
            user_id: '',
            password: '',
            assign_vehicle_personnel: false,
            options: [
                { value: null, text: this.$t('PLEASE_SELECT'), disabled: false },
            ],
            isPassValidation: false,
            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },
        };
    },
    created() {
        this.getListRole();
    },
    methods: {
        backToUserList() {
            this.$router.push('/master-manager/user-master');
        },
        validation() {
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
            } else if (!validPassword(this.password)) {
                this.$toast.warning({
                    content: this.$t('REQUIRE_PASSWORD_WITH_VALIDATE'),
                });
            } else {
                this.isPassValidation = true;
            }
        },
        async doSignUp() {
            this.overlay.show = true;
            this.isProcess = true;
            this.validation();
            if (this.isPassValidation === true) {
                const URL = API_URLS.urlcreateUser;
                const DATA = {
                    role: this.user_role,
                    name: this.employee_name,
                    id: this.user_id,
                    password: this.password,
                };
                await createUser(URL, DATA)
                    .then((res) => {
                        if (res.code === 200) {
                            this.$toast.success({
                                content: this.$t('USER_MANAGEMENT.TOAST_CREATE_SUCCESS'),
                            });

                            this.$router.push('/master-manager/user-master');
                        } else if ([422].includes(res.code)) {
                            this.$toast.warning({
                                content: res.message,
                            });
                        } else {
                            this.$toast.danger({
                                content: this.$t('TOAST_HAVE_ERROR'),
                            });
                        }
                    })
                    .catch((error) => {
                        this.$toast.danger({
                            content: error.message,
                        });
                    });
            }
            this.isProcess = false;
            this.overlay.show = false;
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
    },
};
</script>

<style lang="scss" scoped>
@import "@/scss/variables.scss";

.content-body {
    min-height: calc(100vh - 89px);

    margin: auto;
    max-width: 60%;
    margin-top: 60px;
    margin-bottom: 60px;

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
}
</style>
