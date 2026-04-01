<template>
	<b-overlay
		:show="overlay.show"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
		:blur="overlay.blur"
		:rounded="overlay.sm"
	>
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p class="text-overlay">{{ $t("PLEASE_WAIT") }}</p>
			</div>
		</template>

		<div class="customer-master-edit">
			<b-col>
				<div class="customer-master-edit__header">
					<vHeaderPage>
						{{ $t("ROUTER_CUSTOMER_MASTER") }}
					</vHeaderPage>
				</div>

				<div class="customer-master-edit__body">
					<div class="input-customer-name">
						<b-row align-h="center">
							<b-col cols="12" sm="12" md="12" lg="6" xl="6">
								<label for="input-customer-name">
									{{ $t("CUSTOMER_MASTER_TABLE_LABLE_CUSTOMER_NAME") }}
								</label>
								<b-form-input id="input-customer-name" v-model="customerName" :disabled="overlay.show" />

								<b-row>
									<b-col cols="12" sm="6" md="6" lg="6" xl="6">
										<div class="text-center">
											<vButton
												:text-button="$t('BUTTON.BACK')"
												:class-name="'v-button-default'"
												:disabled="overlay.show"
												@click.native="onClickBack()"
											/>
										</div>
									</b-col>
									<b-col cols="12" sm="5" md="6" lg="6" xl="6">
										<div class="text-center">
											<vButton
												:text-button="$t('BUTTON.SAVE')"
												:class-name="'v-button-default btn-registration'"
												:disabled="overlay.show"
												@click.native="onClickSave()"
											/>
										</div>
									</b-col>
								</b-row>
							</b-col>
						</b-row>
					</div>
				</div>
			</b-col>
		</div>
	</b-overlay>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vButton from '@/components/atoms/vButton';

const urlAPI = {
    get: '/customer',
    put: '/customer',
};

import { getOneCustomer, putCustomer } from '@/api/modules/customerMaster';

export default {
    name: 'CustomerMasterEdit',
    components: {
        vHeaderPage,
        vButton,
    },
    data() {
        return {
            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },

            customerName: '',
        };
    },
    created() {
        this.handleGetOneCustomer();
    },
    methods: {
        async handleGetOneCustomer() {
            this.overlay.show = true;

            try {
                const ID = this.$route.params.id;

                if (ID) {
                    const URL = urlAPI.get;

                    const res = await getOneCustomer(`${URL}/${ID}`);

                    if (res.code === 200) {
                        this.customerName = res.data.customer_name || '';
                    }
                } else {
                    this.$toast.warning({
                        content: 'IDが正しくありません',
                    });
                }

                this.overlay.show = false;
            } catch (error) {
                this.$toast.danger({
                    content: error.response.data.message,
                });
            }
        },
        onClickBack() {
            this.$router.push({ name: 'CustomerMaster' }).catch(() => {});
        },
        onClickSave() {
            if (this.validateCreateCustomer()) {
                this.handleUpdateCustomer();
            }
        },
        validateCreateCustomer() {
            if (this.customerName.trim()) {
                if (this.customerName.length <= 20) {
                    return true;
                } else {
                    this.$toast.warning({
                        content: '荷主名は20文字以内で入力ください。',
                    });

                    return false;
                }
            } else {
                this.$toast.warning({
                    content: '荷主名を入力してください。',
                });

                return false;
            }
        },
        async handleUpdateCustomer() {
            try {
                this.overlay.show = true;

                const ID = this.$route.params.id;
                const URL = urlAPI.put;
                const DATA = {
                    customer_name: this.customerName,
                };

                if (ID) {
                    const res = await putCustomer(`${URL}/${ID}`, DATA);

                    if (res.code === 200) {
                        this.$toast.success({
                            content: '編集が完了しました',
                        });

                        this.$router.push({ name: 'CustomerMaster' });
                    }
                }

                this.overlay.show = false;
            } catch (error) {
                this.overlay.show = false;
                this.$toast.danger({
                    content: error.response.data.message,
                });
            }
        },
    },
};
</script>

<style lang="scss" scoped>
	@import "@/scss/variables";

	.text-overlay {
		margin-top: 10px;
	}

	label {
		font-weight: bolder;
		font-size: 22px;
		color: $pale-sky;
		font-weight: 500;
	}

	.customer-master-edit {
		overflow: hidden;
		min-height: calc(100vh - 89px);

		&__header,
		&__body {
			margin-bottom: 20px;
		}

		&__body {
			.input-customer-name {
				margin-top: 150px;
			}

			#input-customer-name {
				margin-bottom: 200px;
			}
		}
		&__handle {
			.v-button-default {
				margin-bottom: 20px;
			}
		}
	}
</style>
