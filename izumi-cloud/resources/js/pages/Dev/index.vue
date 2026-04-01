<template>
	<div class="dev">
		<b-col>
			<div class="dev__language">
				<h2>Languages</h2>
			</div>
		</b-col>

		<b-col>
			<b-row>
				<b-col>
					<div :class="{ 'dev__btn-lang': true, 'dev__choose-lang': language === 'en' }">
						<b-button @click="setLanguage('en')">English</b-button>
					</div>
				</b-col>

				<b-col>
					<div :class="{ 'dev__btn-lang': true, 'dev__choose-lang': language === 'ja' }">
						<b-button @click="setLanguage('ja')">Japanese</b-button>
					</div>
				</b-col>
			</b-row>
		</b-col>
	</div>
</template>

<script>
export default {
    name: 'PageDev',
    computed: {
        language() {
            return this.$store.getters.language;
        },
    },
    methods: {
        setLanguage(lang) {
            this.$store.dispatch('app/setLanguage', lang)
                .then(() => {
                    this.$i18n.locale = lang;
                    this.$toast.success({
                        content: 'Change language successfully',
                    });
                })
                .catch(() => {
                    this.$toast.danger({
                        content: 'Language change failed',
                    });
                });
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables';

    .dev {
        &__language {
            text-align: center;
        }

        &__btn-lang {
            text-align: center;

            button {
                min-width: 150px;
                border: none;

                &:active {
                    background-color: $west-side !important;
                }
            }
        }

        &__choose-lang {
            button {
                background-color: $west-side;
            }
        }
    }
</style>

