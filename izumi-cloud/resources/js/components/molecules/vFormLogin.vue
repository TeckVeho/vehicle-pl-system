<template>
	<div class="form-login">
		<div class="item-input">
			<vLabel :text-label="$t('LOGIN_ID')" />
			<vInput
				:id="'user_id'"
				v-model="Account.id"
				:type="'text'"
				:placeholder="$t('LOGIN_PLACEHOLDER_ID')"
				:disabled="disableInput"
				:hit-enter="handleHitEnter"
			/>
		</div>

		<div class="item-input">
			<vLabel :text-label="$t('LOGIN_PASSWORD')" />
			<b-input-group>
				<vInput
					:id="'password'"
					v-model="Account.password"
					:type="showPassword ? 'text' : 'password'"
					:placeholder="$t('LOGIN_PLACEHOLDER_PASSWORD')"
					:disabled="disableInput"
					:hit-enter="handleHitEnter"
				/>
				<b-input-group-append @click="toggleShowPassword()">
					<b-input-group-text class="show-password">
						<i v-if="!showPassword" class="fas fa-eye" />
						<i v-if="showPassword" class="fas fa-eye-slash" />
					</b-input-group-text>
				</b-input-group-append>
			</b-input-group>
		</div>
	</div>
</template>

<script>
import vLabel from '@/components/atoms/vLabel';
import vInput from '@/components/atoms/vInput';

export default {
    name: 'VFormLogin',
    components: {
        vLabel,
        vInput,
    },
    props: {
        disableInput: {
            type: Boolean,
            require: false,
            default: false,
        },
    },
    data() {
        return {
            Account: {
                id: '',
                password: '',
            },

            showPassword: false,
        };
    },
    computed: {
        idUserChange() {
            return this.Account.id;
        },
        passswordUserChange() {
            return this.Account.password;
        },
    },
    watch: {
        idUserChange() {
            this.emitData();
        },
        passswordUserChange() {
            this.emitData();
        },
    },
    created() {
        this.emitData();
    },
    methods: {
        emitData() {
            this.$bus.emit('emitFormLogin', this.Account);
        },
        handleHitEnter() {
            this.$bus.emit('LOGIN_HIT_ENTER');
        },
        toggleShowPassword() {
            this.showPassword = !this.showPassword;
        },
    },
};
</script>

<style lang="scss" scoped>
.form-login {
    .item-input {
        margin-bottom: 10px;
    }
}

.show-password {
    cursor: pointer;
    width: 45px;
}
</style>
