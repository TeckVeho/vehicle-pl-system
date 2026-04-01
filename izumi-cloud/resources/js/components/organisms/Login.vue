<template>
	<div class="login-page">
		<div class="container-fluid login">
			<div class="container">
				<div class="col-xl-6 col-lg-6 mx-auto login-container">
					<div class="login-form">
						<vFormLogin :disable-input="isProcess" />
					</div>

					<div class="login-button-submit">
						<vButton :class-name="'v-button-default login-btn'" :disabled="isProcess" @click.native="handleLogin()">
							<template #custom>
								<i class="fas fa-sign-in icon-form" />
								{{ $t('LOGIN_BUTTON_TEXT') }}
							</template>
						</vButton>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
const urlAPI = {
    urlLogin: `/auth/login`,
};

import { postLogin } from '@/api/modules/login';
import { validateNumberMoreThanZero, validEmptyOrWhiteSpace } from '@/utils/validate';
import { parseToken } from '@/utils/handleToken';

import vFormLogin from '@/components/molecules/vFormLogin';
import vButton from '@/components/atoms/vButton';

export default {
    name: 'LoginOrganisms',
    components: {
        vFormLogin,
        vButton,
    },
    data() {
        return {
            isProcess: false,
            Account: {
                id: '',
                password: '',
            },
        };
    },
    created() {
        this.$bus.on('emitFormLogin', (value) => {
            this.Account = value;
        });

        this.$bus.on('LOGIN_HIT_ENTER', () => {
            this.handleLogin();
        });
    },
    destroyed() {
        this.$bus.off('emitFormLogin');
        this.$bus.off('LOGIN_HIT_ENTER');
    },
    methods: {
        async handleLogin() {
            this.isProcess = true;

            if (validateNumberMoreThanZero(this.Account.id) && !validEmptyOrWhiteSpace(this.Account.password)) {
                const URL = urlAPI.urlLogin;

                const DATA = {
                    id: this.Account.id,
                    password: this.Account.password,
                };

                await this.callApiLogin(URL, DATA);
            } else {
                this.$toast.warning({
                    content: this.$t('ERROR_VALIDATE_ID_PASSWORD'),
                });
            }

            this.isProcess = false;
        },

        async callApiLogin(URL, DATA) {
            try {
                const response = await postLogin(URL, DATA);

                if (response.code === 200) {
                    const TOKEN = response.data.access_token;

                    const PROFILE = response.data.profile;
                    const ROLES = response.data.roles;
                    const EXP_TOKEN = parseToken(TOKEN);

                    const USER = {
                        id: PROFILE.id || '',
                        uuid: PROFILE.uuid || '',
                        name: PROFILE.name || '',
                        email: PROFILE.email || '',
                        supervisor_email: PROFILE.supervisor_email || '',
                        department_code: PROFILE.department_code || '',
                        department: PROFILE.department || '',
                        role: PROFILE.role || '',
                        roles: ROLES || [],
                        expToken: EXP_TOKEN.exp || '',
                    };
                    localStorage.setItem('refresh_token', `${TOKEN}`);

                    this.$store.dispatch('user/saveLogin', { USER, TOKEN })
                        .then(() => {
                            this.$store.dispatch('role/generateRoutes', { roles: ROLES, permissions: [] })
                                .then((routes) => {
                                    for (let route = 0; route < routes.length; route++) {
                                        this.$router.addRoute(routes[route]);
                                    }

                                    this.$toast.success({
                                        content: this.$t('LOGIN_SUCCESS'),
                                    });

                                    // if (USER.roles[0] === 'crew') {
                                    //     this.$router.push({ path: '/master-manager/course-master' });
                                    // } else {
                                    //     this.$router.push({ path: '/' });
                                    // }

                                    this.$router.push({ path: '/transportation' });
                                });
                        });
                }
            } catch (error) {
                if (error.response.status === 401) {
                    this.$toast.danger({
                        content: error.response.data.message,
                    });
                } else if (error.response.status === 422) {
                    this.$toast.danger({
                        content: error.response.data.message,
                    });
                } else {
                    this.$toast.danger({
                        content: error.response.data.message,
                    });
                }
            }
        },
    },
};
</script>

<style lang="scss" scoped>

.login-page {
    height: calc(100vh - 150px);

    .login {
        margin-top: 150px;

        .login-button-submit {
            text-align: center;
            margin-top: 40px;
        }

        .icon-form {
            margin-right: 5px;
        }
    }
}
</style>
