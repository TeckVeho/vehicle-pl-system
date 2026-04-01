<template>
	<b-row class="footer-functional-buttons" lg="2" md="1" sm="1">
		<b-col>
			<vButton
				:text-button="$t('BUTTON.BACK')"
				:class-name="'v-button-default'"
				:disabled="isProcess"
				@click.native="backToUserList()"
			/>
		</b-col>
		<b-col>
			<vButton
				:text-button="$t('BUTTON.SIGN_UP')"
				:class-name="'v-button-default'"
				:disabled="isProcess"
				@click.native="doSignUp()"
			/>
		</b-col>
	</b-row>
</template>

<script>
import vButton from '@/components/atoms/vButton';

export default {
    name: 'UserFooterFunctionalButtons',
    components: {
        vButton,
    },
};
</script>

<style>

</style>
