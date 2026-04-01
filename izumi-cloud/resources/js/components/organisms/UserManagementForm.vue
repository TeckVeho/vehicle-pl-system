<template>
	<div class="content-body">
		<b-row>
			<slot>
				<b-col class="input-row" cols="12">
					<vLabel
						:class-name="'user-regis-label'"
						:text-label="$t('USER_MANAGEMENT.USER_ROLE')"
					/>
					<vSelect
						v-model="user_role"
						:data-options="options"
						:disable="isProcess"
						:placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_USER_ROLE')"
					/>
				</b-col>
				<b-col class="input-row" cols="12">
					<vLabel
						:class-name="'user-regis-label'"
						:text-label="$t('USER_MANAGEMENT.EMPLOYEE_NAME')"
					/>
					<vInput
						v-model="employee_name"
						:class-name="'user-regis-input'"
						:disabled="isProcess"
						:placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_EMPLOYEE_NAME')"
					/>
				</b-col>
				<b-col class="input-row" cols="12">
					<vLabel
						:class-name="'user-regis-label'"
						:text-label="$t('USER_MANAGEMENT.USER_ID')"
					/>
					<vInput
						v-model="user_id"
						:type="'number'"
						:class-name="'user-regis-input'"
						:placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_USER_ID')"
						:disabled="isProcess"
					/>
				</b-col>
				<b-col class="input-row" cols="12">
					<vLabel
						:class-name="'user-regis-label'"
						:text-label="$t('USER_MANAGEMENT.PASSWORD')"
					/>
					<vInput
						v-model="password"
						:type="'password'"
						:class-name="'user-regis-input'"
						:placeholder="$t('USER_MANAGEMENT.PLACEHOLDER_PASSWORD')"
						:disabled="isProcess"
					/>
				</b-col>
				<b-col class="input-row" cols="12">
					<b-row class="footer-functional-buttons" lg="2" md="1" sm="1">
						<b-col>
							<slot name="first-functional-button" />
							<vButton
								:text-button="''"
								:class-name="'v-button-default'"
								:disabled="isProcess"
								@click.native="backToUserList()"
							/>
						</b-col>
						<b-col>
							<slot name="seconde-functional-button" />
							<vButton
								:text-button="$t('BUTTON.SIGN_UP')"
								:class-name="'v-button-default'"
								:disabled="isProcess"
								@click.native="doSignUp()"
							/>
						</b-col>
					</b-row>
				</b-col>
			</slot>
		</b-row>
	</div>
</template>

<script>

export default {
    name: 'UserManagementForm',
    props: {
        isProcess: {
            type: Boolean,
            require: true,
            default: false,
        },
    },
};
</script>

<style lang="scss" scoped>

</style>
