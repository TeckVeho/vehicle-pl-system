<template>
	<div class="department-master-index">
		<b-overlay :show="overlay.show" :blur="overlay.blur" :rounded="overlay.sm" :variant="overlay.variant" :opacity="overlay.opacity">
			<template #overlay>
				<div class="text-center">
					<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
					<p style="margin-top: 10px">{{ $t('PLEASE_WAIT') }}</p>
				</div>
			</template>

			<div class="header">
				<vHeaderPage>{{ $t("ROUTER_DEPARTMENT") }}</vHeaderPage>
			</div>

			<div class="department-document mt-3">
				<b-row>
					<b-col class="text-right d-flex justify-content-end mr-5">
						<div class="download-file mr-3">
							<vButton
								:class="'btn-export'"
								@click.native="exportCSV()"
							>
								<i class="far fa-download icon-history" />
								{{ $t('CSV_EXPORT') }}
							</vButton>
						</div>

					</b-col>
				</b-row>
			</div>

			<div class="table-department-master-holder">
				<b-table-simple
					hover
					striped
					bordered
					outlined
					responsive="sm"
					no-border-collapse
					class="table-department-master"
				>
					<b-thead>
						<b-th class="dragging-th">
							<i class="fas fa-expand-arrows-alt" />
						</b-th>
						<b-th class="department-id-th">拠点ID</b-th>
						<b-th class="department-name-th">拠点名</b-th>
						<b-th class="prefecture-th">都道府県</b-th>
						<b-th class="post-code">郵便番号</b-th>
						<b-th class="address-th">住所</b-th>
						<b-th class="tel">電話番号</b-th>
						<b-th class="edit-th">編集</b-th>
					</b-thead>

					<b-tbody v-if="items.length === 0">
						<b-tr>
							<b-td colspan="5">{{ $t('TABLE_EMPTY') }}</b-td>
						</b-tr>
					</b-tbody>

					<draggable
						v-else
						tag="tbody"
						:list="items"
						handle=".handle"
						:animation="100"
						@change="dragChanged"
						@end="dragging = false"
						@start="dragging = true"
						:component-data="component_data"
						draggable=".department-master-tr"
						:class="{ [`cursor-grabbing`]: dragging === true }"
					>
						<b-tr v-for="(item, indexIndex) in items" :key="indexIndex" class="department-master-tr">
							<b-td class="dragging-td handle">
								<i class="fas fa-bars" />
							</b-td>

							<b-td class="department-id-td">
								<span>{{ item['id'] }}</span>
							</b-td>

							<b-td class="department-name-td">
								<span>{{ item['name'] }}</span>
							</b-td>

							<b-td class="prefecture-td">
								<span>{{ item['province_name'] }}</span>
							</b-td>

							<b-td class="address-td">
								<span>{{ item['post_code'] }}</span>
							</b-td>

							<b-td class="address-td">
								<span>{{ item['address'] }}</span>
							</b-td>

							<b-td class="address-td">
								<span>{{ item['tel'] }}</span>
							</b-td>

							<b-td class="edit-td">
								<i class="fas fa-pen" @click="handleNavigateToEditScreen(item['id'])" />
							</b-td>
						</b-tr>
					</draggable>
				</b-table-simple>
			</div>
		</b-overlay>
	</div>
</template>

<script>
import draggable from 'vuedraggable';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vButton from '@/components/atoms/vButton';

import { getListDepartment, changeOrder } from '@/api/modules/department_master';

const urlAPI = {
    apiGetListDepartment: '/department',
    apiChangeOrder: '/department/change-order',
    exportCsvFile: '/department/export',
};

export default {
    name: 'DepartmentMasterIndex',
    components: {
        draggable,
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

            date: this.$store.getters.yearMonthPickerDriverRecorder['date'],
            year: this.$store.getters.yearMonthPickerDriverRecorder['year'],
            month: this.$store.getters.yearMonthPickerDriverRecorder['month'],
            day: this.$store.getters.yearMonthPickerDriverRecorder['day'],

            items: [],

            dragging: false,

            list_new_order: [],

            component_data: {
                props: {
                    type: 'transition',
                    name: 'flip-list',
                },
            },
            file: '',
        };
    },
    created() {
        this.handleGetListDepartment();
    },
    methods: {
        async exportCSV() {
            // const month = this.$store.getters.yearMonthPickerDriverRecorder;
            const URL = `/api${urlAPI.exportCsvFile}`;
            await fetch(URL, {
                headers: {
                    'Accept-Language': this.$store.getters.language,
                    'Authorization': this.$store.getters.token,
                    'accept': 'application/json',
                },
            }).then(async(res) => {
                let filename = `拠点マスタ_${this.year}年${this.month}月.csv`;
                filename = filename.replaceAll('"', '');
                await res.blob().then((res) => {
                    this.file = res;
                });
                const fileURL = window.URL.createObjectURL(this.file);
                const fileLink = document.createElement('a');
                fileLink.href = fileURL;
                fileLink.setAttribute('download', filename);
                document.body.appendChild(fileLink);
                fileLink.click();
            })
                .catch(() => {
                    this.$toast?.danger?.({ content: this.$t('ERROR_EXPORT_CSV') });
                });
            this.file = '';
        },
        dragChanged() {
            this.list_new_order = [];

            const newList = [...this.items].map((item, index) => {
                const newSort = index;

                item.hasChanged = item.sortOrder !== newSort;

                if (item.hasChanged) {
                    item.sortOrder = newSort;
                }

                return item;
            });

            this.items = newList;

            const orderData = [];

            for (let index = 0; index < this.items.length; index++) {
                orderData.push(this.items[index].id);
            }

            this.list_new_order = [...orderData];

            this.handleChangeOrder();
        },
        async handleGetListDepartment() {
            this.overlay.show = true;

            try {
                const URL = `${urlAPI.apiGetListDepartment}`;

                const DATA = [];

                const response = await getListDepartment(URL, DATA);

                if (response['code'] === 200) {
                    this.items = response['data'];
                } else {
                    this.$toast.warning({
                        content: response['message'],
                    });
                }
            } catch (error) {
                console.log('[ERROR]', error);
            }

            this.overlay.show = false;
        },
        async handleChangeOrder() {
            // this.overlay.show = true;

            const DATA = {
                list_department: this.list_new_order,
            };

            const URL = `${urlAPI.apiChangeOrder}`;

            try {
                const response = await changeOrder(URL, DATA);

                if (response['code'] === 200) {
                    // console.log(response);
                } else {
                    this.$toast.warning({
                        content: response['massage'],
                    });
                }
            } catch (error) {
                console.log('[ERROR]', error);
            }

            // this.overlay.show = false;
        },
        handleNavigateToEditScreen(id) {
            this.$router.push({ path: `/master-manager/department-master/edit/${id}` });
        },
    },
};
</script>

<style lang="scss" scoped>
.table-department-master-holder {
    margin-top: 20px;

    .table-department-master table {
        border-collapse: separate;
        border-spacing: 0 !important;

        th {
            text-align: center;
            color: #FFFFFF;
            background-color: #0F0448;
        }

        .dragging-th {
            width: 60px;
        }

        .edit-th {
            width: 100px;
        }

        .department-id-th {
            width: 80px;
        }

        .department-name-th {
            width: 150px;
        }

        .prefecture-th {
            width: 150px;
        }

        td {
            color: #000000;
            text-align: center;
            background-color: #FFFFFF;
        }

        .dragging-td {
            width: 60px;
        }

        .edit-td {
            width: 100px;

            &>i:hover {
                cursor: pointer;
            }
        }

        .department-id-td {
            width: 80px;
        }

        .department-name-td {
            width: 200px;
        }

        .prefecture-td {
            width: 200px;
        }

        .handle:hover {
            cursor: move;
        }
    }
}
</style>
