<template>
	<div>
		<b-row class="notification mx-auto">
			<b-col cols="12" class="text-center">
				<span class="izumi-logo">Izumi</span>
			</b-col>

			<b-col cols="12" class="text-center">
				<span class="text-notification">パスワード設定が完了しました。</span>
				<br>
				<br>
				<span class="text-notification">アプリのログイン画面から</span>
				<br>
				<span class="text-notification">設定したパスワードでログインが可能です。</span>
			</b-col>
		</b-row>
	</div>
</template>

<script>
export default {
    name: 'NotificationScreen',
    data() {
        return {};
    },
    methods: {},
};
</script>

<style lang="scss" scoped>
.izumi-logo {
    text-transform: uppercase;
    color: #1534a1;
    font-size: 64px;
    font-weight: 900;
}

.notification {
    margin-top: 10%;
}

.text-notification {
    color: #1534a1;
    font-weight: 900;
}

@media screen and (max-width: 768px) {
    .notification {
        margin-top: 50%;
    }
}
</style>
