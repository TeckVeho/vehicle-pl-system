<template>
	<div>
		<b-row class="register-password mx-auto">
			<b-col cols="12" class="text-center">
				<span class="izumi-logo">Izumi</span>
			</b-col>

			<b-col cols="12">
				<b-input-group>
					<b-form-input
						v-model="password"
						:type="isShowPassword === true ? 'text' : 'password'"
						placeholder="新しいパスワード"
					/>
					<b-input-group-append is-text>
						<i
							:class="
								isShowPassword === true
									? 'fas fa-eye'
									: 'fas fa-eye-slash'
							"
							@click="showPassword()"
						/>
					</b-input-group-append>
				</b-input-group>
			</b-col>

			<b-col cols="12" class="mt-3">
				<b-input-group>
					<b-form-input
						v-model="confirm_password"
						:type="
							isShowConfirmPassword === true ? 'text' : 'password'
						"
						placeholder="パスワード再入力(確認用)"
					/>
					<b-input-group-append is-text>
						<i
							:class="
								isShowConfirmPassword === true
									? 'fas fa-eye'
									: 'fas fa-eye-slash'
							"
							@click="showConfirmPassword()"
						/>
					</b-input-group-append>
				</b-input-group>
			</b-col>
		</b-row>

		<b-row class="mt-3">
			<b-col cols="12" class="text-center">
				<b-button class="button-submit" @click="register()">
					<b-spinner
						v-if="isLoading"
						:disabled="isLoading"
						variant="primary"
						type="grow"
					/>
					<span v-else>パスワードを設定</span>
				</b-button>
			</b-col>
		</b-row>
	</div>
</template>

<script>
import { postRegister } from '@/api/modules/register';
import { validateRegister } from './validation';

const urlAPI = {
    apiRegister: '/register-password',
};

export default {
    name: 'RegisterScreen',
    data() {
        return {
            password: '',
            confirm_password: '',
            isShowPassword: false,
            isShowConfirmPassword: false,
            isLoading: false,
        };
    },
    methods: {
        showPassword() {
            this.isShowPassword = !this.isShowPassword;
        },

        showConfirmPassword() {
            this.isShowConfirmPassword = !this.isShowConfirmPassword;
        },

        async register() {
            this.isLoading = true;

            try {
                const DATA = {
                    password: this.password,
                    confirm_password: this.confirm_password,
                    value: this.$router.currentRoute.query.value,
                };

                if (validateRegister(DATA) === true) {
                    await postRegister(
                        urlAPI.apiRegister,
                        DATA
                    );

                    this.$router.push({ name: 'Notification' });
                }
            } catch (error) {
                this.$toast.danger({
                    content: this.$t('TOAST_HAVE_ERROR'),
                });
            }

            this.isLoading = false;
        },
    },
};
</script>

<style lang="scss" scoped>
.izumi-logo {
    text-transform: uppercase;
    color: #1534a1;
    font-size: 64px;
    font-weight: 900;
}

.register-password {
    margin-top: 10%;
    max-width: 500px;
}

.button-submit {
    margin-top: 50px;
    background-color: #FFFFFF;
    color: #1534a1;
    border-radius: 15px;
    min-width: 200px;
    min-height: 45px;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;

    &:hover {
        opacity: .8;
        color: #FFFFFF;
        background-color: #1534a1;
    }

    &:active {
        color: #FFFFFF;
        background-color: #1534a1;
    }

    &:focus {
        color: #FFFFFF;
        background-color: #1534a1;
    }
}

::v-deep .fas:hover {
    cursor: pointer;
}

::v-deep .input-group-text {
    min-height: 45px;
    background-color: #ffffff !important;
    border-left: 0 !important;
    border-top-right-radius: 15px;
    border-bottom-right-radius: 15px;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
}

::v-deep .form-control {
    min-height: 45px;
    border-right: 0 !important;
    border-radius: 15px;
    box-shadow: rgba(0, 0, 0, 0.24) -4px 3px 8px;
}

@media screen and (max-width: 768px) {
    .register-password {
        margin-top: 50%;
    }
}
</style>
