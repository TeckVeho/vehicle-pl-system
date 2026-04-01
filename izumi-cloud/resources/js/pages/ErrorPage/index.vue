<template>
	<b-col>
		<div class="error-page">
			<div class="error-page__container">
				<b-row>
					<b-col cols="12" sm="12" md="12" lg="12" xl="12">
						<div class="error-page-detail">
							<div class="error-page-detail__oops">
								おっと!
							</div>
							<div class="error-page-detail__headline">
								このページに入ることができません
							</div>
							<div class="error-page-detail__info">
								入力したURLが正しいことを確認してください。下のボタンをクリックしてホームページに戻るか、エラーレポートを送信してください。
							</div>

							<vButton
								:text-button="'戻る'"
								:class-name="'v-button-default'"
								@click.native="goBack()"
							/>
						</div>
					</b-col>
				</b-row>
			</div>
		</div>
	</b-col>
</template>

<script>
import vButton from '@/components/atoms/vButton';

export default {
    name: 'Page404',
    components: {
        vButton,
    },
    computed: {
        is_expired() {
            return this.$store.getters.token;
        },
    },
    methods: {
        async goBack() {
            if (this.is_expired) {
                this.$router.push({ path: '/' });
            } else {
                await this.$store.dispatch('user/doLogout');

                this.$toast.success({
                    content: this.$t('LOGOUT_SUCCESS'),
                });

                this.$router.push({ path: '/login' });
            }
        },
    },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>

.error-page {
    margin-top: 200px;

    &__container {
        overflow: hidden;
        margin: 0 auto;

        .error-page-detail {
            padding: 30px 0;

            overflow: hidden;

            &__oops {
                font-size: 32px;
                font-weight: bold;
                line-height: 40px;
                color: #052c50;
                opacity: 0;
                margin-bottom: 20px;
                animation-name: slideUp;
                animation-duration: 0.5s;
                animation-fill-mode: forwards;
            }

            &__headline {
                font-size: 20px;
                line-height: 24px;
                color: #222;
                font-weight: bold;
                opacity: 0;
                margin-bottom: 10px;
                animation-name: slideUp;
                animation-duration: 0.5s;
                animation-delay: 0.1s;
                animation-fill-mode: forwards;
            }

            &__info {
                font-size: 13px;
                line-height: 21px;
                color: grey;
                opacity: 0;
                margin-bottom: 30px;
                animation-name: slideUp;
                animation-duration: 0.5s;
                animation-delay: 0.2s;
                animation-fill-mode: forwards;
            }

            @keyframes slideUp {
                0% {
                    transform: translateY(60px);
                    opacity: 0;
                }
                100% {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
        }
    }
}
</style>
