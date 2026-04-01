<template>
	<div class="premium-rate-master">
		<b-overlay
			:show="overlay.show"
			:blur="overlay.blur"
			:rounded="overlay.sm"
			:variant="overlay.variant"
			:opacity="overlay.opacity"
		>
			<template #overlay>
				<div class="text-center">
					<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
					<p style="margin-top: 10px">{{ $t("PLEASE_WAIT") }}</p>
				</div>
			</template>

			<div class="header">
				<vHeaderPage>{{ $t("INSURANCE_RATE_MASTER") }}</vHeaderPage>
			</div>

			<b-row class="mt-3 mb-3 m-0 p-0">
				<b-col cols="12">
					<b-button class="button-change-history" @click="openModalHistory()">
						<span>{{ $t('INSURANCE_RATE_MASTER_SCREEN.CHANGE_HISTORY') }}</span>
					</b-button>
				</b-col>
			</b-row>

			<div class="table-holder">
				<b-table-simple id="table-premium-rate-master" bordered outlined responsive="sm" no-border-collapse>
					<b-thead>
						<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.KINDS') }}</th>
						<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.RATE_NAME') }}</th>
						<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.CHANGE_BEFORE') }}</th>
						<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.AFTER_CHANGE') }}</th>
						<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.APPLICATION_DATE') }}</th>
						<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.EDIT') }}</th>
					</b-thead>

					<b-tbody v-if="vItems.length > 0">
						<b-tr v-for="(item, index) in vItems" :key="`tr-${index}`">
							<b-td>{{ item.kinds }}</b-td>
							<b-td>{{ item.name }}</b-td>
							<b-td>{{ item.current_rate }}</b-td>
							<b-td>{{ item.change_rate }}</b-td>
							<b-td>{{ handleConvertDateFormat(item.applicable_date) }}</b-td>
							<b-td>
								<i class="fas fa-pen" @click="navigateToEditScreen(item.id)" />
							</b-td>
						</b-tr>
					</b-tbody>

					<b-tbody v-else>
						<b-tr>
							<b-td colspan="6">
								<span>{{ $t('TABLE_EMPTY') }}</span>
							</b-td>
						</b-tr>
					</b-tbody>
				</b-table-simple>
			</div>

			<b-modal
				id="modal-cf"
				v-model="modalHistoryDisplayStatus"
				centered
				size="xl"
				hide-footer
				:static="true"
				no-close-on-esc
				no-close-on-backdrop
				content-class="modal-custom-body"
				header-class="modal-custom-header"
				footer-class="modal-custom-footer"
			>
				<div class="table-history-holder">
					<b-table-simple id="table-premium-rate-master-history" bordered outlined responsive="sm" no-border-collapse>
						<b-thead>
							<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.KINDS') }}</th>
							<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.RATE_NAME') }}</th>
							<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.CHANGE_BEFORE') }}</th>
							<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.AFTER_CHANGE') }}</th>
							<th>{{ $t('INSURANCE_RATE_MASTER_SCREEN.APPLICATION_DATE') }}</th>
						</b-thead>

						<b-tbody v-if="vHistoryItems.length > 0">
							<b-tr v-for="(item, index) in vHistoryItems" :key="`tr-${index}`">
								<b-td>{{ item.kinds }}</b-td>
								<b-td>{{ item.name }}</b-td>
								<b-td>{{ item.current_rate }}</b-td>
								<b-td>{{ item.change_rate }}</b-td>
								<b-td>{{ handleConvertDateFormat(item.applicable_date) }}</b-td>
							</b-tr>
						</b-tbody>

						<b-tbody v-else>
							<b-tr>
								<b-td colspan="6">
									<span>{{ $t('TABLE_EMPTY') }}</span>
								</b-td>
							</b-tr>
						</b-tbody>
					</b-table-simple>
				</div>
			</b-modal>
		</b-overlay>
	</div>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';

import { getInsuranceRateList, getInsuranceRateHistory } from '@/api/modules/insuranceRateMaster';

const urlAPI = {
    urlGetInsuranceRateList: '/insurance-rate',
    urlGetInsuranceRateHistory: '/insurance-rate/list/history',
};

export default {
    name: 'InsuranceRateMasterIndex',
    components: {
        vHeaderPage,
    },
    data() {
        return {
            overlay: {
                opacity: 1,
                show: false,
                blur: '1rem',
                rounded: 'sm',
                variant: 'light',
            },

            vItems: [],

            vHistoryItems: [],

            modalHistoryDisplayStatus: false,
        };
    },
    created() {
        this.getInsuranceRateMasterList();
    },
    methods: {
        async openModalHistory() {
            await this.getInsuranceRateHistory();
        },
        navigateToEditScreen(id = '') {
            if (id) {
                this.$router.push({ name: 'InsuranceRateMasterEdit', params: { id: id }});
            }
        },
        async getInsuranceRateMasterList() {
            const URL = `${urlAPI.urlGetInsuranceRateList}`;

            try {
                const response = await getInsuranceRateList(URL);

                if (response.code === 200) {
                    this.vItems = response.data;
                }
            } catch (error) {
                console.log(error);
            }

            this.overlay.show = false;
        },
        async getInsuranceRateHistory() {
            const URL = `${urlAPI.urlGetInsuranceRateHistory}`;

            try {
                const response = await getInsuranceRateHistory(URL);

                if (response.code === 200) {
                    this.vHistoryItems = response.data;
                }
            } catch (error) {
                console.log(error);
            }

            this.modalHistoryDisplayStatus = true;
        },
        handleConvertDateFormat(date) {
            let result = '';

            if (date) {
                const listIngredients = date.split('-');

                result = `${listIngredients[0]}-${listIngredients[1]}`;
            }

            return result;
        },
    },
};
</script>

<style lang="scss" scoped>
::-webkit-scrollbar {
  width: 5px;
  height: 5px;
  border-radius: 45px;
}

::v-deep .table-responsive-sm {
  margin-bottom: 0px !important;
}

.premium-rate-master {
  .button-change-history {
    float: right;
    border: none;
    height: 45px;
    width: 200px;
    color: white;
    font-weight: bold;
    text-align: center;
    background-color: #FB8C00;
  }

  .table-holder {
    height: 600px;
    overflow-y: auto;
    margin-left: 15px;
    margin-right: 15px;

    #table-premium-rate-master {
      border-spacing: 0px;
      border-collapse: separate;
      margin-bottom: 0px !important;

      th {
        top: 0px;
        color: white;
        font-weight: bold;
        text-align: center;
        position: sticky !important;
        background-color: #0F0448;
      }

      td {
        text-align: center;
      }

      i {
        &:hover {
          cursor: pointer;
        }
      }
    }
  }

  .table-history-holder {
    height: 600px;
    overflow-y: auto;
    margin-left: 15px;
    margin-right: 15px;

    #table-premium-rate-master-history {
      border-spacing: 0px;
      border-collapse: separate;
      margin-bottom: 0px !important;

      th {
        top: 0px;
        color: white;
        font-weight: bold;
        text-align: center;
        position: sticky !important;
        background-color: #0F0448;
      }

      td {
        text-align: center;
      }
    }
  }
}
</style>
