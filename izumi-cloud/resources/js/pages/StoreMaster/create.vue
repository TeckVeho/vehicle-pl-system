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

		<div class="store-master-create">
			<div class="store-master-create__header">
				<vHeaderPage>
					{{ $t("ROUTER_STORE_MASTER") }}
				</vHeaderPage>
			</div>

			<vDropdownView :dropdown-title="list_dropdown_title">
				<template #dropdown-content>
					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<label for="input-store-name">{{ $t("STORE_MASTER_TABLE_LABLE_STORE_NAME") }}</label>
							<b-form-input id="input-store-name" v-model="store_name" :disabled="overlay.show" />
						</b-col>

						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-business-classification">{{ $t( "BUSINESS_CLASSIFICATION" ) }}</label>
							<b-form-input id="input-business-classification" v-model="business_classification" :disabled="overlay.show" />
						</b-col>

						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-delivery-destination-code">{{ $t( "DELIVERY_DESTINATION_CODE" ) }}</label>
							<b-form-input id="input-delivery-destination-code" v-model="delivery_destination_code" type="number" :disabled="overlay.show" @keydown.native="onlyNumberInput" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-delivery-destination-name-kana">{{ $t( "DELIVERY_DESTINATION_NAME_KANA" ) }}</label>
							<b-form-input id="input-delivery-destination-name-kana" v-model="delivery_destination_name_kana" :disabled="overlay.show" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-delivery-destination-name">{{ $t( "DELIVERY_DESTINATION_NAME" ) }}</label>
							<b-form-input id="input-delivery-destination-name" v-model="delivery_destination_name" :disabled="overlay.show" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-post-code">{{ $t( "POST_CODE" ) }}</label>
							<b-form-input id="input-post-code" v-model="post_code" type="text" :disabled="overlay.show" :formatter="formatterPostCode" @keydown.native="inputPostCode" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-tel">{{ $t( "TEL" ) }}</label>
							<b-form-input id="input-tel" v-model="tel" type="text" :disabled="overlay.show" :formatter="formatterTelephone" @keydown.native="inputPostCode" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-large-address">{{ $t( "FIRST_ADDRESS" ) }}</label>
							<b-form-input id="input-large-address" v-model="first_address" :disabled="overlay.show" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-small-address">{{ $t( "SECOND_ADDRESS" ) }}</label>
							<b-form-input id="input-small-address" v-model="second_address" :disabled="overlay.show" />
						</b-col>

						<b-col cols="12" sm="12" md="6" lg="12" xl="12" class="mt-3">
							<label for="input-display-pass-code">{{ $t( "PASSCODE" ) }}</label>
							<b-form-input id="input-display-pass-code" v-model="pass_code" type="number" @keydown.native="onlyNumberInput" />
						</b-col>
					</b-row>
				</template>

				<template #dropdown-content-1>
					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-delivery-frequency">納品頻度</label>
							<b-form-input id="input-delivery-frequency" v-model="delivery_frequency" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-quantity-per-delivery">1回当り納品数量</label>

							<b-input-group>
								<template #prepend>
									<b-input-group-text>約</b-input-group-text>
								</template>

								<b-form-input id="input-quantity-per-delivery" v-model="quantity_per_delivery" />

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

								<b-form-input id="input-scheduled-time" v-model="scheduled_time" />

								<template #append>
									<b-input-group-text>分</b-input-group-text>
								</template>
							</b-input-group>
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-vehicle-height-width">車両高・幅の規制</label>
							<b-form-select id="input-vehicle-height-width" v-model="vehicle_height_width" :options="general_dropdown_fields" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-SAMPLE-ID">納品時間指定</label>

							<div class="d-flex flex-row mobile-column">
								<span style="margin: 8px; color: red;" class="no-break">1便</span>
								<b-form-input id="input-specify-delivery-time-first-hour" v-model="first_hour" />
								<span style="margin: 8px;">:</span>
								<b-form-input id="input-specify-delivery-time-first-minute" v-model="first_minute" />
								<span style="margin: 8px;">前</span>
								<b-form-input id="input-specify-delivery-time-first-sub-minute-one" v-model="first_sub_minute_one" />
								<span style="margin: 8px;" class="no-break">分後</span>
								<b-form-input id="input-specify-delivery-time-first-sub-minute-two" v-model="first_sub_minute_two" />
								<span style="margin: 8px;">分</span>
							</div>

							<div class="d-flex flex-row mobile-column mt-3">
								<span style="margin: 8px; color: red;" class="no-break">2便</span>
								<b-form-input id="input-specify-delivery-time-second-hour" v-model="second_hour" />
								<span style="margin: 8px;">:</span>
								<b-form-input id="input-specify-delivery-time-second-minute" v-model="second_minute" />
								<span style="margin: 8px;">前</span>
								<b-form-input id="input-specify-delivery-time-second-sub-minute-one" v-model="second_sub_minute_one" />
								<span style="margin: 8px;" class="no-break">分後</span>
								<b-form-input id="input-specify-delivery-time-second-sub-minute-two" v-model="second_sub_minute_two" />
								<span style="margin: 8px;">分</span>
							</div>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-height-width">高さ(m)/幅(m)</label>

							<div class="d-flex flex-row mobile-column">
								<span style="margin: 8px;" class="no-break">高さ</span>
								<b-form-input id="input-height" v-model="height" type="number" />
								<span style="margin: 15px 5px 0px 5px;">m</span>
							</div>

							<div class="d-flex flex-row mobile-column mt-3">
								<span style="margin: 8px;" class="no-break">
									幅<span style="color: #ffffff;">さ</span>
								</span>
								<b-form-input id="input-width" v-model="width" type="number" />
								<span style="margin: 15px 5px 0px 5px;">m</span>
							</div>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-parking-place">駐車場所指定</label>
							<b-form-select id="input-parking-place" v-model="parking_place" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-1">特記事項</label>
							<b-form-input id="input-note-1" v-model="note_1" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-delivery-slip">納品伝票</label>
							<b-form-select id="input-delivery-slip" v-model="delivery_slip" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-2">特記事項</label>
							<b-form-input id="input-note-2" v-model="note_2" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-daisha">台車使用</label>
							<b-form-select id="input-daisha" v-model="daisha" :options="daisha_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-3">特記事項</label>
							<b-form-input id="input-note-3" v-model="note_3" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-place">置場</label>
							<b-form-input id="input-place" v-model="place" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-4">置場の特記事項</label>
							<b-form-input id="input-note-4" v-model="note_4" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-empty-recovery">空回収</label>
							<b-form-input id="input-empty-recovery" v-model="empty_recovery" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-key">鍵の使用</label>
							<b-form-select id="input-key" v-model="key" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-5">特記事項</label>
							<b-form-input id="input-note-5" v-model="note_5" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-security">セキュリティ使用</label>
							<b-form-select id="input-security" v-model="security" :options="general_dropdown_fields" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-cancel-method">解除方法</label>
							<b-form-input id="input-cancel-method" v-model="cancel_method" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-grace-time">猶予時間</label>
							<b-form-input id="input-grace-time" v-model="grace_time" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-company-name">会社名</label>
							<b-form-input id="input-company-name" v-model="company_name" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-tel-number-2">TEL</label>
							<b-form-input id="input-tel-number-2" v-model="tel_number_2" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-inside-rule">施設内ルール</label>
							<b-form-select id="input-inside-rule" v-model="inside_rule" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-license">許可証</label>
							<b-form-input id="input-license" v-model="license" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-reception-or-entry">受付/記入</label>
							<b-form-input id="input-reception-or-entry" v-model="reception_or_entry" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-cerft-required">照明必要性</label>
							<b-form-select id="input-cerft-required" v-model="cerft_required" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-6">特記事項</label>
							<b-form-input id="input-note-6" v-model="note_6" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-elevator">エレベーター使用</label>
							<b-form-select id="input-elevator" v-model="elevator" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-7">特記事項</label>
							<b-form-input id="input-note-7" v-model="note_7" />
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-waiting-place">待機場所</label>
							<b-form-select id="input-waiting-place" v-model="waiting_place" :options="general_dropdown_fields" />
						</b-col>

						<b-col cols="6" sm="6" md="6" lg="6" xl="6" class="mt-3">
							<label for="input-note-8">特記事項</label>
							<b-form-input id="input-note-8" v-model="note_8" />
						</b-col>
					</b-row>
				</template>

				<template #dropdown-content-2>
					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12">
							<label for="input-delivery-manual">納品手順</label>

							<div class="d-flex flex-row">
								<b-form-input id="input-delivery-manual mr-3" v-model="delivery" placeholder="入力してください" @keydown.enter="handleCreateDelivery()" />

								<b-button class="add-delivery-manual-btn ml-3" @click="handleCreateDelivery()">
									<i class="fas fa-plus-circle" />
								</b-button>
							</div>

							<div v-if="delivery_manual.length" class="d-flex flex-column mt-3" style="border: 1px solid #dddddd; padding: 10px;">
								<div
									v-for="(item, index) in delivery_manual"
									:key="`delivery-${index}`"
									class="d-flex flex-row w-100 align-content-between justify-content-between"
								>
									<span>{{ item }}</span>
									<i class="fas fa-minus-circle remove-delivery-manual-btn" @click="handleRemoveDelivery(index)" />
								</div>
							</div>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-delivery-frequency">納品経路図</label>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
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
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<b-form-file ref="deliveryRoute" accept=".jpg, .png" style="display: none;" @change="previewImage($event, 1)" />
							<b-button class="upload-button" @click="handleSelectFile(1)">
								<i class="far fa-folder-upload ml-2 mt-1" style="font-size: 18px !important;" />
								<span>画像をアップロード</span>
							</b-button>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-delivery-route-map-other-remark">その他・注意事項</label>
							<b-form-input id="input-delivery-route-map-other-remark" v-model="delivery_route_map_other_remark" />
						</b-col>
					</b-row>
				</template>

				<template #dropdown-content-3>
					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-delivery-frequency">納品経路図</label>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
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
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<b-form-file ref="parkingPosition1" accept=".jpg, .png" style="display: none;" @change="previewImage($event, 2)" />
							<b-button class="upload-button" @click="handleSelectFile(2)">
								<i class="far fa-folder-upload ml-2 mt-1" style="font-size: 18px !important;" />
								<span>画像をアップロード</span>
							</b-button>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-parking-position-1-other-remark">その他・注意事項</label>
							<b-form-input id="input-parking-position-1-other-remark" v-model="parking_position_1_other_remark" />
						</b-col>
					</b-row>
				</template>

				<template #dropdown-content-4>
					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-delivery-frequency">納品経路図</label>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
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
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<b-form-file ref="parkingPosition2" accept=".jpg, .png" style="display: none;" @change="previewImage($event, 3)" />
							<b-button class="upload-button" @click="handleSelectFile(3)">
								<i class="far fa-folder-upload ml-2 mt-1" style="font-size: 18px !important;" />
								<span>画像をアップロード</span>
							</b-button>
						</b-col>
					</b-row>

					<b-row align-h="center">
						<b-col cols="12" sm="12" md="12" lg="12" xl="12" class="mt-3">
							<label for="input-parking-position-2-other-remark">その他・注意事項</label>
							<b-form-input id="input-parking-position-2-other-remark" v-model="parking_position_2_other_remark" />
						</b-col>
					</b-row>
				</template>
			</vDropdownView>

			<div class="d-flex flex-row justify-content-between align-content-between footer-button">
				<vButton
					:text-button="$t('BUTTON.BACK')"
					:class-name="'v-button-default'"
					:disabled="overlay.show"
					@click.native="onClickBack()"
				/>

				<vButton
					:text-button="$t('BUTTON.SIGN_UP')"
					:class-name="'v-button-default btn-registration'"
					:disabled="overlay.show"
					@click.native="onClickSave()"
				/>
			</div>
		</div>
	</b-overlay>
</template>

<script>
import vButton from '@/components/atoms/vButton';
import vDropdownView from '@/components/atoms/vDropdownView';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';

import { postStore } from '@/api/modules/storeMaster';
import { onlyNumberInput, inputPostCode } from '@/utils/onlyNumberInput';

const urlAPI = {
    postStore: '/store',
};

export default {
    name: 'StoreMasterCreate',
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

            onlyNumberInput,
            inputPostCode,

            business_classification: '',
            store_name: '',
            delivery_destination_code: '',
            delivery_destination_name_kana: '',
            delivery_destination_name: '',
            post_code: '',
            tel: '',
            first_address: '',
            second_address: '',
            pass_code: '',

            list_dropdown_title: ['基本情報', '納品情報', '納品手順', 'ルート周辺情報', '駐車場情報'],

            general_dropdown_fields: [
                { value: null, text: '選択してください', disabled: true },
                { value: 0, text: '無し', disabled: false },
                { value: 1, text: '有り', disabled: false },
            ],

            daisha_dropdown_fields: [
                { value: null, text: '選択してください', disabled: true },
                { value: 1, text: '指定無し', disabled: false },
                { value: 2, text: '普通台車', disabled: false },
                { value: 3, text: 'ゴム台車', disabled: false },
                { value: 4, text: '指定台車', disabled: false },
                { value: 5, text: '手持ち', disabled: false },
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

            daisha: '',
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

            delivery: '',
            delivery_manual: [],

            delivery_route_map_path: '',
            delivery_route_map_other_remark: '',

            parking_position_1_file_path: '',
            parking_position_1_other_remark: '',

            parking_position_2_file_path: '',
            parking_position_2_other_remark: '',

            default_image_path: 'http://thaibinhtv.vn/thumb/640x400/assets/images/imgstd.jpg',
        };
    },
    methods: {
        onClickBack() {
            this.$router.push({ name: 'StoreMaster' });
        },
        onClickSave() {
            this.handleClickSave();
        },
        handleClickSave() {
            if (this.validateData()) {
                this.handlePostStore();
            }
        },
        validateData() {
            let result = false;

            if (!this.store_name || !String(this.store_name).trim()) {
                this.$toast.warning({
                    content: '店舗名を入力してください。',
                });
            } else if (this.store_name.length > 20) {
                this.$toast.warning({
                    content: '店舗名は20文字以内で入力ください。',
                });
            } else if (this.delivery_destination_name && this.delivery_destination_name.length > 100) {
                this.$toast.warning({
                    content: '納品先名は50文字以内で入力してください。',
                });
            } else if (this.post_code.length > 8) {
                this.$toast.warning({
                    content: '郵便番号は半角英数字8文字以内で入力してください。',
                });
            } else if (this.first_address.length > 20) {
                this.$toast.warning({
                    content: '大住所は20文字以内で入力してください。',
                });
            } else if (this.second_address.length > 50) {
                this.$toast.warning({
                    content: '小住所は50文字以内で入力してください。',
                });
            } else if (this.tel.length > 13) {
                this.$toast.warning({
                    content: 'Telは半角英数字13文字以内で入力してください。',
                });
            } else if (this.delivery_frequency && this.delivery_frequency.length > 20) {
                this.$toast.warning({
                    content: '納品頻度は20文字以内で入力してください。',
                });
            } else if (parseInt(this.quantity_per_delivery) > 1000) {
                this.$toast.warning({
                    content: '1回当り納品数量は半角英数字で最大1000を入力してください。',
                });
            } else if (parseInt(this.scheduled_time) > 60) {
                this.$toast.warning({
                    content: '納品所要時間は半角英数字で最大60を入力してください。',
                });
            } else if (parseInt(this.vehicle_height_width) > 999) {
                this.$toast.warning({
                    content: 'は半角英数字で最大999を入力してください。',
                });
            } else if (this.note_1 && this.note_1.length > 30) {
                this.$toast.warning({
                    content: '特記事項は30文字以内で入力してください。',
                });
            } else if (this.note_2 && this.note_2.length > 30) {
                this.$toast.warning({
                    content: '特記事項は30文字以内で入力してください。',
                });
            } else if (this.note_3 && this.note_3.length > 50) {
                this.$toast.warning({
                    content: '特記事項は50文字以内で入力してください。',
                });
            } else if (this.place && this.place.length > 50) {
                this.$toast.warning({
                    content: '置場は50文字以内で入力してください。',
                });
            } else if (this.note_4 && this.note_4.length > 50) {
                this.$toast.warning({
                    content: '特記事項は50文字以内で入力してください。',
                });
            } else if (this.empty_recovery && this.empty_recovery.length > 50) {
                this.$toast.warning({
                    content: '空回収は50文字以内で入力してください。',
                });
            } else if (this.note_5 && this.note_5.length > 50) {
                this.$toast.warning({
                    content: '特記事項は50文字以内で入力してください。',
                });
            } else if (this.cancel_method && this.cancel_method.length > 50) {
                this.$toast.warning({
                    content: '解除方法は50文字以内で入力してください。',
                });
            } else if (this.grace_time && this.grace_time.length > 50) {
                this.$toast.warning({
                    content: '猶予時間は50文字以内で入力してください。',
                });
            } else if (this.company_name && this.company_name.length > 50) {
                this.$toast.warning({
                    content: '会社名は50文字以内で入力してください。',
                });
            } else if (this.tel_number_2 && this.tel_number_2.length > 13) {
                this.$toast.warning({
                    content: 'Telは半角英数字13文字以内で入力してください。',
                });
            } else if (this.license && this.license.length > 50) {
                this.$toast.warning({
                    content: '許可証は50文字以内で入力してください。',
                });
            } else if (this.reception_or_entry && this.reception_or_entry.length > 50) {
                this.$toast.warning({
                    content: '受付/ 記入は50文字以内で入力してください。',
                });
            } else if (this.cerft_required && this.cerft_required.length > 50) {
                this.$toast.warning({
                    content: '照明必要性は50文字以内で入力してください。',
                });
            } else if (this.elevator && this.elevator.length > 50) {
                this.$toast.warning({
                    content: '特記事項は50文字以内で入力してください。',
                });
            } else if (this.note_6 && this.note_6.length > 50) {
                this.$toast.warning({
                    content: '特記事項は50文字以内で入力してください。',
                });
            } else {
                result = true;
            }

            return result;
        },
        handleProcessFormData(value) {
            if (value === null || value === 'null') {
                value = '';
            }

            return value;
        },
        async handlePostStore() {
            this.overlay.show = true;

            try {
                const URL = urlAPI.postStore;

                const FORM_DATA = new FormData();

                FORM_DATA.append('bussiness_classification', this.handleProcessFormData(this.business_classification));
                FORM_DATA.append('store_name', this.handleProcessFormData(this.store_name));
                FORM_DATA.append('delivery_destination_code', this.handleProcessFormData(this.delivery_destination_code));
                FORM_DATA.append('destination_name_kana', this.handleProcessFormData(this.delivery_destination_name_kana));
                FORM_DATA.append('destination_name', this.handleProcessFormData(this.delivery_destination_name));
                FORM_DATA.append('post_code', this.handleProcessFormData(this.post_code));
                FORM_DATA.append('tel_number', this.handleProcessFormData(this.tel));
                FORM_DATA.append('address_1', this.handleProcessFormData(this.first_address));
                FORM_DATA.append('address_2', this.handleProcessFormData(this.second_address));

                if (this.pass_code) {
                    FORM_DATA.append('pass_code', parseInt(this.pass_code));
                } else {
                    FORM_DATA.append('pass_code', '');
                }

                FORM_DATA.append('delivery_frequency', this.handleProcessFormData(this.delivery_frequency));
                FORM_DATA.append('quantity_delivery', this.handleProcessFormData(this.quantity_per_delivery));
                FORM_DATA.append('scheduled_time_first', this.handleProcessFormData(this.scheduled_time));

                FORM_DATA.append('first_sd_time', `${this.handleProcessFormData(this.handleCorrectTimeInput(this.first_hour))}:${this.handleProcessFormData(this.handleCorrectTimeInput(this.first_minute))}`);
                FORM_DATA.append('first_sd_sub_min_one', this.handleProcessFormData(this.first_sub_minute_one));
                FORM_DATA.append('first_sd_sub_min_second', this.handleProcessFormData(this.first_sub_minute_two));

                FORM_DATA.append('second_sd_time', `${this.handleProcessFormData(this.handleCorrectTimeInput(this.second_hour))}:${this.handleProcessFormData(this.handleCorrectTimeInput(this.second_minute))}`);
                FORM_DATA.append('second_sub_min_one', this.handleProcessFormData(this.second_sub_minute_one));
                FORM_DATA.append('second_sub_min_second', this.handleProcessFormData(this.second_sub_minute_two));

                FORM_DATA.append('vehicle_height_width', this.handleProcessFormData(this.vehicle_height_width));
                FORM_DATA.append('height', this.handleProcessFormData(this.height));
                FORM_DATA.append('width', this.handleProcessFormData(this.width));

                FORM_DATA.append('parking_place', this.handleProcessFormData(this.parking_place));
                FORM_DATA.append('note_1', this.handleProcessFormData(this.note_1));

                FORM_DATA.append('delivery_slip', this.handleProcessFormData(this.delivery_slip));
                FORM_DATA.append('note_2', this.handleProcessFormData(this.note_2));

                FORM_DATA.append('daisha', this.handleProcessFormData(this.daisha));
                FORM_DATA.append('note_3', this.handleProcessFormData(this.note_3));

                FORM_DATA.append('place', this.handleProcessFormData(this.place));
                FORM_DATA.append('note_4', this.handleProcessFormData(this.note_4));

                FORM_DATA.append('empty_recovery', this.handleProcessFormData(this.empty_recovery));

                FORM_DATA.append('key', this.handleProcessFormData(this.key));
                FORM_DATA.append('note_5', this.handleProcessFormData(this.note_5));

                FORM_DATA.append('security', this.handleProcessFormData(this.security));
                FORM_DATA.append('cancel_method', this.handleProcessFormData(this.cancel_method));

                FORM_DATA.append('grace_time', this.handleProcessFormData(this.grace_time));
                FORM_DATA.append('company_name', this.handleProcessFormData(this.company_name));
                FORM_DATA.append('tel_number_2', this.handleProcessFormData(this.tel_number_2));
                FORM_DATA.append('inside_rule', this.handleProcessFormData(this.inside_rule));
                FORM_DATA.append('license', this.handleProcessFormData(this.license));
                FORM_DATA.append('reception_or_entry', this.handleProcessFormData(this.reception_or_entry));

                FORM_DATA.append('cerft_required', this.handleProcessFormData(this.cerft_required));
                FORM_DATA.append('note_6', this.handleProcessFormData(this.note_6));

                FORM_DATA.append('elevator', this.handleProcessFormData(this.elevator));
                FORM_DATA.append('note_7', this.handleProcessFormData(this.note_7));

                FORM_DATA.append('waiting_place', this.handleProcessFormData(this.waiting_place));
                FORM_DATA.append('note_8', this.handleProcessFormData(this.note_8));

                const TEMP_DELIVERY_MANUAL = [this.delivery_manual];

                if (TEMP_DELIVERY_MANUAL) {
                    for (let i = 0; i < TEMP_DELIVERY_MANUAL.length; i++) {
                        FORM_DATA.append('delivery_manual[]', TEMP_DELIVERY_MANUAL[i]);
                    }
                }

                if (this.delivery_route_map_path && this.delivery_route_map_path !== this.default_image_path) {
                    FORM_DATA.append('delivery_route_map_path', new Blob([this.delivery_route_map_path], { type: 'image' }));
                }

                FORM_DATA.append('delivery_route_map_other_remark', this.handleProcessFormData(this.delivery_route_map_other_remark));

                if (this.parking_position_1_file_path && this.parking_position_1_file_path !== this.default_image_path) {
                    FORM_DATA.append('parking_position_1_file_path', new Blob([this.parking_position_1_file_path], { type: 'image' }));
                }

                FORM_DATA.append('parking_position_1_other_remark', this.handleProcessFormData(this.parking_position_1_other_remark));

                if (this.parking_position_2_file_path && this.parking_position_2_file_path !== this.default_image_path) {
                    FORM_DATA.append('parking_position_2_file_path', new Blob([this.parking_position_2_file_path], { type: 'image' }));
                }

                FORM_DATA.append('parking_position_2_other_remark', this.handleProcessFormData(this.parking_position_2_other_remark));

                FORM_DATA.append('last_updated_at', new Date().toISOString());

                const res = await postStore(URL, FORM_DATA);

                if (res.code === 200) {
                    this.$toast.success({ content: '新規登録に成功しました' });
                    this.$router.push({ name: 'StoreMaster' });
                }

                this.overlay.show = false;
            } catch (error) {
                this.overlay.show = false;

                this.$toast.danger({
                    content: error.response.data.message,
                });
            }
        },
        handleCorrectTimeInput(value) {
            let result = '';

            if (value) {
                if (Number.parseInt(value) < 10 && value.length === 2) {
                    result = '0' + value.toString();
                } else {
                    result = value.toString();
                }
            }

            return result;
        },
        formatterPostCode(post_code) {
            const SPLIT = post_code.split('');
            const VALIDATE_NUMBER = SPLIT.filter((item) => item >= 0);
            const _postCode = VALIDATE_NUMBER.join('');

            if (VALIDATE_NUMBER.length > 7) {
                return '';
            }

            if (VALIDATE_NUMBER.length === 7) {
                return `${_postCode.slice(0, 3)}-${_postCode.slice(3, 7)}`;
            }

            const IS_EXIT = SPLIT.filter((item) => item === '-');

            if (IS_EXIT.length >= 2) {
                return '';
            }

            return post_code;
        },
        formatterTelephone(tel) {
            const SPLIT = tel.split('');
            const VALIDATE_NUMBER = SPLIT.filter((item) => item >= 0);
            const _tel = VALIDATE_NUMBER.join('');

            if (VALIDATE_NUMBER.length > 11) {
                return '';
            }

            if (VALIDATE_NUMBER.length === 11) {
                return `${_tel.slice(0, 3)}-${_tel.slice(3, 7)}-${_tel.slice(7, 13)}`;
            }

            const IS_EXIT = SPLIT.filter((item) => item >= 0);

            return IS_EXIT.join('');
        },
        handleSelectFile(target) {
            if (target === 1) {
                this.$refs.deliveryRoute.$el.querySelector('input[type="file"]').click();
            } else if (target === 2) {
                this.$refs.parkingPosition1.$el.querySelector('input[type="file"]').click();
            } else {
                this.$refs.parkingPosition2.$el.querySelector('input[type="file"]').click();
            }
        },
        previewImage(event, target) {
            const file = event.target.files[0];

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = (e) => {
                    switch (target) {
                    case 1:
                        this.delivery_route_map_path = e.target.result;
                        break;
                    case 2:
                        this.parking_position_1_file_path = e.target.result;
                        break;
                    case 3:
                        this.parking_position_2_file_path = e.target.result;
                        break;
                    default:
                        break;
                    }
                };

                reader.readAsDataURL(file);
            } else {
                switch (target) {
                case 1:
                    this.delivery_route_map_path = '';
                    this.$refs.deliveryRoute.$el.querySelector('input[type="file"]').value = null;
                    break;
                case 2:
                    this.parking_position_1_file_path = '';
                    this.$refs.parkingPosition1.$el.querySelector('input[type="file"]').value = null;
                    break;
                case 3:
                    this.parking_position_2_file_path = '';
                    this.$refs.parkingPosition2.$el.querySelector('input[type="file"]').value = null;
                    break;
                default:
                    break;
                }
            }
        },
        handleCreateDelivery() {
            if (this.delivery === '') {
                this.$toast.warning({ content: '配達が必要です。' });
            } else if (this.delivery.length > 50) {
                this.$toast.warning({ content: '納品手順は50文字以内で入力してください' });
            } else {
                if (this.delivery_manual.length === 0 || this.delivery_manual.length < 20) {
                    this.delivery_manual.push(this.delivery);
                    this.delivery = '';
                } else {
                    this.$toast.warning({ content: '納品手順は最大20件を入力してください' });
                }
            }
        },
        handleRemoveDelivery(index) {
            const newArray = [...this.delivery_manual];
            newArray.splice(index, 1);
            this.delivery_manual = newArray;
        },
    },
};
</script>

<style lang="scss" scoped>
	@import "@/scss/variables";
  .remove-delivery-manual-btn {
    width: 28px;
    height: 28px;
    color: red;

    &:hover {
      opacity: .6;
      cursor: pointer;
    }
  }
  .add-delivery-manual-btn {
    background-color: #82CD47;

    &:hover {
      opacity: .6;
    }
  }
  .upload-button {
    width: 100%;
    height: 40px;
    color: #000000;
    font-size: 14px;
    background-color: #FFFFFF;
    border-radius: 0px !important;
    border: 2px dashed #DDDDDD !important;

    &:hover {
      opacity: .6;
    }

    &:focus {
      outline: none !important;
    }
  }
  .no-break {
    word-break: keep-all;
  }
	.text-overlay {
		margin-top: 10px;
	}

  label {
    color: #212529;
    font-size: 16px;
    font-weight: 500;
  }
  .footer-button {
    padding-left: 50px;
    padding-right: 55px;
  }
	.store-master-create {
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
