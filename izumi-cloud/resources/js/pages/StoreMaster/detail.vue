<template>
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
				<p class="text-overlay">{{ $t("PLEASE_WAIT") }}</p>
			</div>
		</template>

		<div class="store-master-detail">
			<div class="store-master-detail__header">
				<vHeaderPage>
					{{ $t("ROUTER_STORE_MASTER") }}
				</vHeaderPage>
			</div>

			<vDropdownView :dropdown-title="list_dropdown_title">
				<template #dropdown-content>
					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-store-name">{{ $t( "STORE_MASTER_TABLE_LABLE_STORE_NAME" ) }}</label>
							<b-form-input id="input-store-name" v-model="storeName" :disabled="true" />
						</b-col>

						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-business-classification">{{ $t( "BUSINESS_CLASSIFICATION" ) }}</label>
							<b-form-input id="input-business-classification" v-model="businessClassification" :disabled="true" />
						</b-col>

						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-delivery-destination-code">{{ $t( "DELIVERY_DESTINATION_CODE" ) }}</label>
							<b-form-input id="input-delivery-destination-code" v-model="deliveryDestinationCode" :disabled="true" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-delivery-destination-name-kana">{{ $t( "DELIVERY_DESTINATION_NAME_KANA" ) }}</label>
							<b-form-input id="input-delivery-destination-name-kana" v-model="deliveryDestinationNameKana" :disabled="true" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-delivery-destination-name">{{ $t( "DELIVERY_DESTINATION_NAME" ) }}</label>
							<b-form-input id="input-delivery-destination-name" v-model="deliveryDestinationName" :disabled="true" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-post-code">{{ $t( "POST_CODE" ) }}</label>
							<b-form-input id="input-post-code" v-model="postCode" :disabled="true" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-tel">{{ $t( "TEL" ) }}</label>
							<b-form-input id="input-tel" v-model="tel" :disabled="true" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-large-address">{{ $t( "FIRST_ADDRESS" ) }}</label>
							<b-form-input id="input-large-address" v-model="firstAddress" :disabled="true" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-small-address">{{ $t( "SECOND_ADDRESS" ) }}</label>
							<b-form-input id="input-small-address" v-model="secondAddress" :disabled="true" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="12" xl="12" class="mt-3">
							<label for="input-display-pass-code">{{ $t( "PASSCODE" ) }}</label>
							<b-form-input id="input-display-pass-code" v-model="passCode" :disabled="true" />
						</b-col>
					</b-row>
				</template>

				<template #dropdown-content-1>
					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-delivery-frequency">納品頻度</label>
							<b-form-input id="input-delivery-frequency" v-model="delivery_frequency" :disabled="true" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-quantity-per-delivery">1回当り納品数量</label>

							<b-input-group>
								<template #prepend>
									<b-input-group-text>約</b-input-group-text>
								</template>

								<b-form-input id="input-quantity-per-delivery" v-model="quantity_per_delivery" :disabled="true" />

								<template #append>
									<b-input-group-text>枚</b-input-group-text>
								</template>
							</b-input-group>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-scheduled-time">納品時間指定</label>

							<b-input-group>
								<template #prepend>
									<b-input-group-text>約</b-input-group-text>
								</template>

								<b-form-input id="input-scheduled-time" v-model="scheduled_time" :disabled="true" />

								<template #append>
									<b-input-group-text>分</b-input-group-text>
								</template>
							</b-input-group>
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-vehicle-height-width">車両高・幅の規制</label>
							<b-form-select id="input-vehicle-height-width" v-model="vehicle_height_width" :disabled="true" :options="general_dropdown_fields" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-SAMPLE-ID">納品時間指定</label>

							<div class="d-flex flex-row mobile-column">
								<span style="margin: 8px; color: red;" class="no-break">1便</span>
								<b-form-input id="input-specify-delivery-time-first-hour" v-model="first_hour" :disabled="true" />
								<span style="margin: 8px;">:</span>
								<b-form-input id="input-specify-delivery-time-first-minute" v-model="first_minute" :disabled="true" />
								<span style="margin: 8px;">前</span>
								<b-form-input id="input-specify-delivery-time-first-sub-minute-one" v-model="first_sub_minute_one" :disabled="true" />
								<span style="margin: 8px;" class="no-break">分後</span>
								<b-form-input id="input-specify-delivery-time-first-sub-minute-two" v-model="first_sub_minute_two" :disabled="true" />
								<span style="margin: 8px;">分</span>
							</div>

							<div class="d-flex flex-row mobile-column mt-3">
								<span style="margin: 8px; color: red;" class="no-break">2便</span>
								<b-form-input id="input-specify-delivery-time-second-hour" v-model="second_hour" :disabled="true" />
								<span style="margin: 8px;">:</span>
								<b-form-input id="input-specify-delivery-time-second-minute" v-model="second_minute" :disabled="true" />
								<span style="margin: 8px;">前</span>
								<b-form-input id="input-specify-delivery-time-second-sub-minute-one" v-model="second_sub_minute_one" :disabled="true" />
								<span style="margin: 8px;" class="no-break">分後</span>
								<b-form-input id="input-specify-delivery-time-second-sub-minute-two" v-model="second_sub_minute_two" :disabled="true" />
								<span style="margin: 8px;">分</span>
							</div>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-height-width">高さ(m)/幅(m)</label>

							<div class="d-flex flex-row mobile-column">
								<span style="margin: 8px;" class="no-break">高さ</span>
								<b-form-input id="input-height" v-model="height" :disabled="true" />
								<span style="margin: 15px 5px 0px 5px;">m</span>
							</div>

							<div class="d-flex flex-row mobile-column mt-3">
								<span style="margin: 8px;" class="no-break">
									幅<span style="color: #ffffff;">さ</span>
								</span>
								<b-form-input id="input-width" v-model="width" :disabled="true" />
								<span style="margin: 15px 5px 0px 5px;">m</span>
							</div>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-parking-place">駐車場所指定</label>
							<b-form-select id="input-parking-place" v-model="parking_place" :disabled="true" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-1">特記事項</label>
							<b-form-input id="input-note-1" v-model="note_1" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-delivery-slip">納品伝票</label>
							<b-form-select id="input-delivery-slip" v-model="delivery_slip" :disabled="true" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-2">特記事項</label>
							<b-form-input id="input-note-2" v-model="note_2" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-daisha">台車使用</label>
							<b-form-select id="input-daisha" v-model="daisha" :disabled="true" :options="daisha_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-3">特記事項</label>
							<b-form-input id="input-note-3" v-model="note_3" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-place">置場</label>
							<b-form-input id="input-place" v-model="place" :disabled="true" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-4">置場の特記事項</label>
							<b-form-input id="input-note-4" v-model="note_4" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-empty-recovery">空回収</label>
							<b-form-input id="input-empty-recovery" v-model="empty_recovery" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-key">鍵の使用</label>
							<b-form-select id="input-key" v-model="key" :disabled="true" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-5">特記事項</label>
							<b-form-input id="input-note-5" v-model="note_5" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-security">セキュリティ使用</label>
							<b-form-select id="input-security" v-model="security" :disabled="true" :options="general_dropdown_fields" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-cancel-method">解除方法</label>
							<b-form-input id="input-cancel-method" v-model="cancel_method" :disabled="true" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-grace-time">猶予時間</label>
							<b-form-input id="input-grace-time" v-model="grace_time" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-company-name">会社名</label>
							<b-form-input id="input-company-name" v-model="company_name" :disabled="true" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-tel-number-2">TEL</label>
							<b-form-input id="input-tel-number-2" v-model="tel_number_2" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-inside-rule">施設内ルール</label>
							<b-form-select id="input-inside-rule" v-model="inside_rule" :disabled="true" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-license">許可証</label>
							<b-form-input id="input-license" v-model="license" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-reception-or-entry">受付/記入</label>
							<b-form-input id="input-reception-or-entry" v-model="reception_or_entry" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-cerft-required">照明必要性</label>
							<b-form-select id="input-cerft-required" v-model="cerft_required" :disabled="true" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-6">特記事項</label>
							<b-form-input id="input-note-6" v-model="note_6" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-elevator">エレベーター使用</label>
							<b-form-select id="input-elevator" v-model="elevator" :disabled="true" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-7">特記事項</label>
							<b-form-input id="input-note-7" v-model="note_7" :disabled="true" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-waiting-place">待機場所</label>
							<b-form-select id="input-waiting-place" v-model="waiting_place" :disabled="true" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-8">特記事項</label>
							<b-form-input id="input-note-8" v-model="note_8" :disabled="true" />
						</b-col>
					</b-row>
				</template>

				<template #dropdown-content-2>
					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<label for="input-delivery-manual">納品手順</label>

							<div v-if="delivery_manual.length" class="d-flex flex-column">
								<span v-for="(item, index) in delivery_manual" :key="`delivery-manual-item-${index}`">{{ item }}</span>
							</div>
						</b-col>
					</b-row>

					<b-row align-h="center" class="mt-3">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<label for="input-delivery-frequency">納品経路図</label>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<div class="d-flex justify-content-center align-items-center w-100">
								<template v-if="delivery_route_map_path">
									<img :src="delivery_route_map_path" alt="" class="image-card">
								</template>

								<template v-else>
									<img :src="default_image_path" alt="" class="image-card">
								</template>
							</div>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<label for="input-delivery-route-map-other-remark">その他・注意事項</label>
							<b-form-input id="input-delivery-route-map-other-remark" v-model="delivery_route_map_other_remark" :disabled="true" />
						</b-col>
					</b-row>
				</template>

				<template #dropdown-content-3>
					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<label for="input-delivery-frequency">納品経路図</label>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<div class="d-flex justify-content-center align-items-center w-100">
								<template v-if="parking_position_1_file_path">
									<img :src="parking_position_1_file_path" alt="" class="image-card">
								</template>

								<template v-else>
									<img :src="default_image_path" alt="" class="image-card">
								</template>
							</div>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<label for="input-parking-position-1-other-remark">その他・注意事項</label>
							<b-form-input id="input-parking-position-1-other-remark" v-model="parking_position_1_other_remark" :disabled="true" />
						</b-col>
					</b-row>
				</template>

				<template #dropdown-content-4>
					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<label for="input-delivery-frequency">納品経路図</label>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<div class="d-flex justify-content-center align-items-center w-100">
								<template v-if="parking_position_2_file_path">
									<img :src="parking_position_2_file_path" alt="" class="image-card">
								</template>

								<template v-else>
									<img :src="default_image_path" alt="" class="image-card">
								</template>
							</div>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<label for="input-parking-position-2-other-remark">その他・注意事項</label>
							<b-form-input id="input-parking-position-2-other-remark" v-model="parking_position_2_other_remark" :disabled="true" />
						</b-col>
					</b-row>
				</template>
			</vdropdownview>

			<div class="d-flex flex-row justify-content-between align-content-between footer-button">
				<vButton
					:text-button="$t('BUTTON.BACK')"
					:class-name="'v-button-default'"
					:disabled="overlay.show"
					@click.native="onClickBack()"
				/>

				<vButton
					:text-button="$t('BUTTON.EDIT')"
					:class-name="'v-button-default btn-to-edit'"
					:disabled="overlay.show"
					@click.native="onClickEdit()"
				/>
			</div>
		</div>
	</b-overlay>
</template>

<script>
import vButton from '@/components/atoms/vButton';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vDropdownView from '@/components/atoms/vDropdownView';

import { getOneStore } from '@/api/modules/storeMaster';

const urlAPI = {
    get: '/store',
    put: '/store',
};

export default {
    name: 'StoreMasterDetail',
    components: {
        vButton,
        vHeaderPage,
        vDropdownView,
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

            businessClassification: '',

            storeName: '',

            deliveryDestinationCode: '',

            deliveryDestinationNameKana: '',

            deliveryDestinationName: '',

            postCode: '',

            tel: '',

            firstAddress: '',

            secondAddress: '',

            passCode: '',

            // 2494
            list_dropdown_title: ['基本情報', '納品情報', '納品手順', 'ルート周辺情報', '駐車場情報'],

            general_dropdown_fields: [
                { value: 0, text: '無し' },
                { value: 1, text: '有り' },
            ],

            daisha_dropdown_fields: [
                { value: 1, text: '指定無し' },
                { value: 2, text: '普通台車' },
                { value: 3, text: 'ゴム台車' },
                { value: 4, text: '指定台車' },
                { value: 5, text: '手持ち' },
            ],

            delivery_frequency: '',
            quantity_per_delivery: '',
            scheduled_time: '',
            vehicle_height_width: '',
            height: '',
            width: '',

            first_hour: '',
            first_minute: '',
            first_sub_minute_one: '',
            first_sub_minute_two: '',

            second_hour: '',
            second_minute: '',
            second_sub_minute_one: '',
            second_sub_minute_two: '',

            parking_place: '',
            note_1: '',

            delivery_slip: '',
            note_2: '',

            daisha: null,
            note_3: '',

            place: '',
            note_4: '',

            empty_recovery: '',
            reception_or_entry: '',

            key: '',
            note_5: '',

            security: '',

            cancel_method: '',
            grace_time: '',

            company_name: '',
            tel_number_2: '',

            inside_rule: '',
            license: '',

            cerft_required: '',
            note_6: '',

            elevator: '',
            note_7: '',

            waiting_place: '',
            note_8: '',

            delivery_manual: '',

            delivery_route_map_path: '',
            delivery_route_map_other_remark: '',

            parking_position_1_file_path: '',
            parking_position_1_other_remark: '',

            parking_position_2_file_path: '',
            parking_position_2_other_remark: '',

            default_image_path: 'http://thaibinhtv.vn/thumb/640x400/assets/images/imgstd.jpg',
        };
    },
    computed: {
        enviroment_path() {
            return process.env.MIX_LARAVEL_PATH;
        },
    },
    created() {
        this.handleGetOneStore();
    },
    methods: {
        async handleGetOneStore() {
            this.overlay.show = true;

            try {
                const ID = this.$route.params.id;

                if (ID) {
                    const URL = urlAPI.get;

                    const res = await getOneStore(`${URL}/${ID}`);

                    const DATA = res.data;

                    if (res.code === 200) {
                        this.businessClassification = DATA.bussiness_classification;
                        this.storeName = DATA.store_name;
                        this.deliveryDestinationCode = DATA.delivery_destination_code;
                        this.deliveryDestinationNameKana = DATA.destination_name_kana;
                        this.deliveryDestinationName = DATA.destination_name;
                        this.postCode = DATA.post_code;
                        this.tel = DATA.tel_number;
                        this.firstAddress = DATA.address_1;
                        this.secondAddress = DATA.address_2;
                        this.passCode = DATA.pass_code;

                        // 2494
                        this.delivery_frequency = DATA.delivery_frequency;
                        this.quantity_per_delivery = DATA.quantity_delivery;
                        this.scheduled_time = DATA.scheduled_time_first;
                        this.vehicle_height_width = DATA.vehicle_height_width;
                        this.height = DATA['height'];
                        this.width = DATA['width'];

                        if (DATA.first_sd_time) {
                            this.first_hour = DATA.first_sd_time.split(':')[0];
                            this.first_minute = DATA.first_sd_time.split(':')[1];
                        } else {
                            this.first_hour = '';
                            this.first_minute = '';
                        }

                        this.first_sub_minute_one = DATA.first_sd_sub_min_one;
                        this.first_sub_minute_two = DATA.first_sd_sub_min_second;

                        if (DATA.second_sd_time) {
                            this.second_hour = DATA.second_sd_time.split(':')[0];
                            this.second_minute = DATA.second_sd_time.split(':')[1];
                        } else {
                            this.second_hour = '';
                            this.second_minute = '';
                        }

                        this.second_sub_minute_one = DATA.second_sub_min_one;
                        this.second_sub_minute_two = DATA.second_sub_min_second;

                        this.parking_place = DATA.parking_place;
                        this.note_1 = DATA.note_1;

                        this.delivery_slip = DATA.delivery_slip;
                        this.note_2 = DATA.note_2;

                        this.daisha = DATA.daisha;
                        this.note_3 = DATA.note_3;

                        this.place = DATA.place;
                        this.note_4 = DATA.note_4;

                        this.empty_recovery = DATA.empty_recovery;
                        this.reception_or_entry = DATA.reception_or_entry;

                        this.key = DATA.key;
                        this.note_5 = DATA.note_5;

                        this.security = DATA.security;

                        this.cancel_method = DATA.cancel_method;
                        this.grace_time = DATA.grace_time;

                        this.company_name = DATA.company_name;
                        this.tel_number_2 = DATA.tel_number_2;

                        this.inside_rule = DATA.inside_rule;
                        this.license = DATA.license;

                        this.cerft_required = DATA.cerft_required;
                        this.note_6 = DATA.note_6;

                        this.elevator = DATA.elevator;
                        this.note_7 = DATA.note_7;

                        this.waiting_place = DATA.waiting_place;
                        this.note_8 = DATA.note_8;

                        this.handleTransformStructure(DATA.delivery_manual);

                        if (DATA.delivery_route_map_path) {
                            this.delivery_route_map_path = `${this.enviroment_path}/storage/${DATA.delivery_route_map_path}`;
                        } else {
                            this.delivery_route_map_path = '';
                        }

                        this.delivery_route_map_other_remark = DATA.delivery_route_map_other_remark;

                        if (DATA.parking_position_1_file_path) {
                            this.parking_position_1_file_path = `${this.enviroment_path}/storage/${DATA.parking_position_1_file_path}`;
                        } else {
                            this.parking_position_1_file_path = '';
                        }

                        this.parking_position_1_other_remark = DATA.parking_position_1_other_remark;

                        if (DATA.parking_position_2_file_path) {
                            this.parking_position_2_file_path = `${this.enviroment_path}/storage/${DATA.parking_position_2_file_path}`;
                        } else {
                            this.parking_position_2_file_path = '';
                        }

                        this.parking_position_2_other_remark = DATA.parking_position_2_other_remark;
                    }
                } else {
                    this.$toast.warning({ content: 'ストアIDが正しくありません' });
                }

                this.overlay.show = false;
            } catch (error) {
                this.$toast.danger({
                    content: error.response.data.message,
                });
            }
        },
        onClickBack() {
            this.$router.push({ name: 'StoreMaster' });
        },
        onClickEdit() {
            if (this.$route.params.id) {
                this.$router.push({ name: 'StoreMasterEdit', params: { id: this.$route.params.id }});
            }
        },
        validateCreateStore() {
            if (this.storeName.trim()) {
                if (this.storeName.length <= 20) {
                    return true;
                } else {
                    this.$toast.warning({
                        content: '店舗名は20文字以内で入力ください。',
                    });

                    return false;
                }
            } else {
                this.$toast.warning({
                    content: '店舗名を入力してください。',
                });

                return false;
            }
        },
        formatterPostCode(postCode) {
            const SPLIT = postCode.split('');
            const VALIDATE_NUMBER = SPLIT.filter((item) => item >= 0);
            const _postCode = VALIDATE_NUMBER.join('');

            const IS_EXIT = SPLIT.filter((item) => item === '-');

            if (IS_EXIT.length) {
                return postCode;
            } else {
                if (VALIDATE_NUMBER.length === 7) {
                    return `${_postCode.slice(0, 3)}-${_postCode.slice(3, 7)}`;
                }
            }

            return postCode;
        },
        formatterTelephone(tel) {
            const SPLIT = tel.split('');
            const VALIDATE_NUMBER = SPLIT.filter((item) => item >= 0);
            const _tel = VALIDATE_NUMBER.join('');

            const IS_EXIT = SPLIT.filter((item) => item === '-');

            if (IS_EXIT.length) {
                return tel;
            } else {
                if (VALIDATE_NUMBER.length === 11) {
                    return `${_tel.slice(0, 3)}-${_tel.slice(3, 7)}-${_tel.slice(7, 11)}`;
                }
            }

            return tel;
        },
        handleTransformStructure(array) {
            const resultArray = [];

            if (array) {
                array.forEach(element => {
                    resultArray.push(element['content']);
                });
            }

            this.delivery_manual = [...resultArray];
        },
    },
};
</script>

<style lang="scss" scoped>
  @import "@/scss/variables";

  .no-break {
    word-break: keep-all;
  }

  .text-overlay {
    margin-top: 10px;
  }

  label {
    font-weight: bolder;
    font-size: 16px;
    color: #212529;
    font-weight: 500;
  }

  .footer-button {
    padding-left: 50px;
    padding-right: 55px;
  }

  .store-master-detail {
    overflow: hidden;
    min-height: calc(100vh - 89px);

    &__header,
    &__body {
      margin-bottom: 20px;

      .input-fields {
        margin: 25px 50px 0px 50px;
      }
    }

    &__handle {
      .v-button-default {
        margin-bottom: 20px;
      }
    }
  }

  .image-card {
    width: auto;
    height: 300px;
  }

  @media (max-width: 860px) {
    .mobile-column {
      flex-direction: column !important;
    }
    .footer-button {
      padding-left: 20px;
      padding-right: 20px;
    }

    .image-card {
      width: auto;
      height: 200px;
    }
  }
</style>
