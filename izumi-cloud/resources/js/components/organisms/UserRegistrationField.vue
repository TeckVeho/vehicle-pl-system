<template>
	<div class="registration-field">
		<b-row>
			<b-col cols="12">
				<vButton
					:text-button="$t('BUTTON.SIGN_UP')"
					:class-name="'btn-radius v-button-default btn-registration'"
					@click.native="doRegistration()"
				/>
			</b-col>
		</b-row>
	</div>
</template>

<script>
import vButton from '@/components/atoms/vButton';
export default {
    name: 'UserRegistrationField',
    components: {
        vButton,
    },
    methods: {
        doRegistration() {
            this.$router.push(
                {
                    path: `/master-manager/user-master-create`,
                }, (onAbort) => {});
        },
    },
};
</script>

<style>
.registration-field {
    margin: 0;
    width: 100%;
}
</style>
