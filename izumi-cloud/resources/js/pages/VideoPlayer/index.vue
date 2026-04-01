<template>
	<div style="height: 100%;">
		<b-overlay
			:show="overlay.show"
			:blur="overlay.blur"
			:rounded="overlay.sm"
			:variant="overlay.variant"
			:opacity="overlay.opacity"
			style="height: 100%"
		>
			<template #overlay>
				<div class="text-center">
					<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
					<p style="margin-top: 10px;">{{ $t('PLEASE_WAIT') }}</p>
				</div>
			</template>

			<vHeaderPage>{{ $t('ROUTER_VIDEO_PLAYER') }}</vHeaderPage>

			<div class="mt-3" style="padding-top: 5px;">
				<vHeaderFilter>
					<template #zone-filter>
						<b-col>
							<b-row>
								<span class="text-clear-all" @click="onClickClearAll()">
									{{ $t('CLEAR_ALL') }}
								</span>
							</b-row>

							<div class="filter-item">
								<b-row class="mb-3">
									<b-col cols="12" class="reset-padding-b-col">
										<b-input-group>
											<b-input-group-prepend is-text class="checkbox-holder">
												<input v-model="filter.title.status" type="checkbox">
											</b-input-group-prepend>

											<b-input-group-prepend is-text class="prepend-filter">
												<span class="prepend-filter-text">キーワード</span>
											</b-input-group-prepend>

											<b-form-input v-model="filter.title.value" placeholder="" style="height: 47px;" :disabled="!filter.title.status" />
										</b-input-group>
									</b-col>
								</b-row>

								<b-row class="mb-3">
									<b-col cols="12" class="reset-padding-b-col">
										<b-input-group class="custom-input-group">
											<b-input-group-prepend is-text class="checkbox-holder">
												<input v-model="filter.tag.status" type="checkbox">
											</b-input-group-prepend>

											<b-input-group-prepend is-text class="prepend-filter">
												<span class="prepend-filter-text">タグ</span>
											</b-input-group-prepend>

											<vMultiselect
												v-model="filter.tag.value"
												:options="tag_options"
												placeholder="選択してください"
												:multiple="true"
												label="text"
												track-by="value"
												class="filter-select"
												:select-label="'選択するにはEnterキーを押してください'"
												:deselect-label="'削除するにはEnterキーを押してください'"
												:selected-label="'選択されました'"
												:searchable="true"
												:close-on-select="false"
												:disabled="!filter.tag.status"
											>
												<template slot="noOptions">
													<span>リストは空です。</span>
												</template>

												<template slot="noResult">
													<span>該当する結果が見つかりません。検索クエリを変更してください。</span>
												</template>
											</vMultiselect>
										</b-input-group>
									</b-col>
								</b-row>
							</div>
						</b-col>

						<div class="zone-btn-apply">
							<vButton :class="'btn-summit-filter'" :text-button="$t('BUTTON.APPLY')" @click.native="onClickApply()" />
						</div>
					</template>
				</vHeaderFilter>
			</div>

			<div class="header-bar mt-3">
				<div class="d-flex flex-row justify-content-end w-100 align-items-center">
					<div class="d-flex flex-row justify-content-between">
						<vButton
							:text-button="'視聴者一括出力'"
							:class-name="'btn-radius v-button-default btn-registration mb-0 mr-3'"
							@click.native="handleDownloadDeliveryRecord()"
						/>

						<vButton
							:text-button="'配信記録'"
							:class-name="'btn-radius v-button-default btn-registration mb-0 mr-3'"
							@click.native="handleShowDeliveryRecord()"
						/>

						<vButton
							:text-button="'配信予定'"
							:class-name="'btn-radius v-button-default btn-registration mb-0 mr-3'"
							@click.native="handleClickAssignMoviesButton()"
						/>

						<vButton
							:text-button="'登録'"
							:class-name="'btn-radius v-button-default btn-registration m-0'"
							@click.native="handleClickRegisterButton()"
						/>
					</div>
				</div>
			</div>

			<div class="my-content mt-3">
				<div v-if="items.length" class="d-flex flex-column w-100" style="padding: 0px 250px;">
					<draggable
						:list="items"
						handle=".handle"
						:animation="100"
						draggable=".drag-cat"
						:class="dragging ? 'w-100 cursor-grabbing' : 'w-100'"
						@change="dragChanged"
						@end="dragging = false"
						@start="dragging = true"
					>
						<div v-for="(item, index) in items" :key="index" class="d-flex flex-row drag-cat item position-relative">
							<div class="d-flex align-items-center p-2" style="margin-right: 20px;">
								<i class="fas fa-bars icon-drag handle" />
							</div>

							<div v-if="item.thumbnail" class="thumbnail">
								<img :src="item.thumbnail.file_url" alt="item-thumbnail" class="thumbnail-img">
								<span class="video-length">{{ item.file_length }}</span>
							</div>

							<div v-else class="thumbnail">
								<b-skeleton class="skeleton-cover" />
								<span class="video-length">{{ item.file_length }}</span>
							</div>

							<div class="toggle-loop-container">
								<b-form-checkbox
									v-model="item.is_loop_enabled"
									switch
									size="sm"
									@change="handleToggleLoopEnabled(item)"
								>
									ループ配信
								</b-form-checkbox>
							</div>

							<div class="video-info">
								<div class="d-flex flex-column">
									<span class="title">{{ item.title }}</span>
									<span class="description">{{ item.content }}</span>
								</div>

								<div class="d-flex flex-row flex-wrap">
									<div v-for="(tag_item, tag_index) in item.tag" :key="`item-${index}-tag-${tag_index}`" class="tag-pill">
										<span>{{ returnTextFromOption(tag_item) }}</span>
									</div>
								</div>
							</div>

							<div class="function-button position-absolute">
								<b-button variant="warning" @click="handleOpenModalEditVideo(item.id)">
									<i class="fas fa-pen" />
								</b-button>

								<b-button class="ml-3" variant="danger" @click="handleOpenModalConfirmDelete(item.id)">
									<i class="fas fa-trash-alt" />
								</b-button>
							</div>
						</div>
					</draggable>
				</div>

				<template v-else>
					<div class="no-data">
						<span class="text-center">データなし</span>
					</div>
				</template>

			</div>

			<div class="d-flex justify-content-center align-items-center mt-3 mb-3">
				<div class="select-per-page">
					<label for="per-page" class="mr-2">1ページ毎の表示数</label>
					<b-form-select
						id="per-page"
						v-model="pagination.per_page"
						size="sm"
						:options="options_per_page"
						@change="handleChangePerPage"
					/>
				</div>

				<vPagination
					:aria-controls="'table-vehicle-master'"
					:current-page="pagination.current_page"
					:per-page="pagination.per_page"
					:total-rows="pagination.total_rows"
					:next-class="'next'"
					:prev-class="'prev'"
					@currentPageChange="handleChangeCurrentPage"
				/>
			</div>

			<b-modal v-model="modal_register_movie" centered size="xl" no-close-on-backdrop @hide="handleCloseModalRegister()">
				<template #modal-title>
					<span style="font-weight: bold;">登録</span>
				</template>

				<div class="d-flex flex-column w-100">
					<div class="d-flex flex-row w-100 mt-3">
						<span class="modal-register-label">タイトル</span>
						<b-form-input v-model="title" />
					</div>

					<div class="d-flex flex-row w-100 mt-3">
						<span class="modal-register-label">詳細</span>
						<b-form-textarea v-model="description" max-rows="10" rows="5" />
					</div>

					<div class="d-flex flex-row w-100 mt-3">
						<span class="modal-register-label">ムービーファイル</span>

						<vuedropzone
							id="customdropzonefile"
							ref="myVueDropzoneFile"
							:use-custom-slot="true"
							:include-styling="false"
							:options="dropzoneFileOptions"
							@vdropzone-file-added="afterAdded($event, 1)"
							@vdropzone-complete="afterComplete($event, 1)"
						>
							<div v-if="is_uploading_video" class="upload-video-input">
								<b-icon icon="arrow-clockwise" animation="spin" font-scale="2" />
								<span>アップロード中...</span>
							</div>

							<div v-else class="upload-video-input">
								<span>
									{{ video_name }}
								</span>
							</div>
						</vueDropzone>
					</div>

					<div v-if="video_source" class="d-flex w-100 justify-content-center align-items-center mt-3">
						<video id="video" :src="video_source" type="video/mp4" controls class="video-preview" />
					</div>

					<div class="d-flex flex-row w-100 mt-3">
						<span class="modal-register-label">サムネイル画像</span>

						<b-form-file
							ref="thumbnailFileRegister"
							v-model="thumbnail_file"
							placeholder="選択してください"
							drop-placeholder="ここにファイルをドロップしてください..."
							accept=".png, .jpg"
							@input="handleSelectImageFile($event, false)"
						/>

						<b-button variant="danger" class="ml-2" @click="handleDeleteThumbnail(1)">
							<i class="far fa-times-circle" />
						</b-button>
					</div>

					<div v-if="thumbnail_url" class="d-flex w-100 justify-content-center align-items-center mt-3">
						<img :src="thumbnail_url" alt="Thumbnail" class="image-preview">
					</div>
				</div>

				<div class="d-flex flex-row w-100 mt-3">
					<span class="modal-register-label">タグ</span>
					<vMultiselect
						v-model="tag"
						:options="tag_options"
						placeholder="選択してください"
						:multiple="true"
						label="text"
						track-by="value"
						:select-label="'選択するにはEnterキーを押してください'"
						:deselect-label="'削除するにはEnterキーを押してください'"
						:selected-label="'選択されました'"
						:searchable="true"
						:close-on-select="false"
					>
						<template slot="noOptions">
							<span>リストは空です。</span>
						</template>

						<template slot="noResult">
							<span>該当する結果が見つかりません。検索クエリを変更してください。</span>
						</template>
					</vMultiselect>
				</div>

				<template #modal-footer>
					<div class="d-flex w-100 justify-content-end align-items-end">
						<b-button :disabled="is_uploading_video" class="button-save" @click="handleClickSaveButton()">
							<span>保存する</span>
						</b-button>
					</div>
				</template>
			</b-modal>

			<b-modal v-model="modal_edit_video" centered size="xl" no-close-on-backdrop @hide="handleCloseModalEditVideo()">
				<template #modal-title>
					<span style="font-weight: bold;">編集</span>
				</template>

				<div class="d-flex flex-column w-100">
					<div class="d-flex flex-row w-100 mt-3">
						<span class="modal-register-label">タイトル</span>
						<b-form-input v-model="video_information.title" />
					</div>

					<div class="d-flex flex-row w-100 mt-3">
						<span class="modal-register-label">詳細</span>
						<b-form-textarea v-model="video_information.content" max-rows="10" rows="5" />
					</div>

					<div class="d-flex flex-row w-100 mt-3">
						<span class="modal-register-label">ムービーファイル</span>

						<vuedropzone
							id="customdropzonefile"
							ref="myVueDropzoneFile"
							:use-custom-slot="true"
							:include-styling="false"
							:options="dropzoneFileOptions"
							@vdropzone-file-added="afterAdded($event, 2)"
							@vdropzone-complete="afterComplete($event, 2)"
						>
							<div v-if="is_uploading_video" class="upload-video-input">
								<b-icon icon="arrow-clockwise" animation="spin" font-scale="2" />
								<span>アップロード中...</span>
							</div>

							<div v-else class="upload-video-input">
								<span>{{ video_information.video_name }}</span>
							</div>
						</vueDropzone>
					</div>

					<div v-if="video_information.video_source" class="d-flex w-100 justify-content-center align-items-center mt-3">
						<video id="video" :src="video_information.video_source" type="video/mp4" controls class="video-preview" />
					</div>

					<div class="d-flex flex-row w-100 mt-3">
						<span class="modal-register-label">サムネイル画像</span>

						<b-form-file
							ref="thumbnailFileEdit"
							v-model="video_information.thumbnail_file"
							:placeholder="video_information.thumbnail_name"
							drop-placeholder="ここにファイルをドロップしてください..."
							accept=".png, .jpg"
							@input="handleSelectImageFile($event, true)"
						/>

						<b-button variant="danger" class="ml-2" @click="handleDeleteThumbnail(2)">
							<i class="far fa-times-circle" />
						</b-button>
					</div>

					<div v-if="video_information.thumbnail_url" class="d-flex w-100 justify-content-center align-items-center mt-3">
						<img :src="video_information.thumbnail_url" alt="Thumbnail" class="image-preview">
					</div>
				</div>

				<div class="d-flex flex-row w-100 mt-3">
					<span class="modal-register-label">タグ</span>
					<vMultiselect
						v-model="list_selected_tags"
						:options="tag_options"
						placeholder="選択してください"
						:multiple="true"
						label="text"
						track-by="value"
						:select-label="'選択するにはEnterキーを押してください'"
						:deselect-label="'削除するにはEnterキーを押してください'"
						:selected-label="'選択されました'"
						:searchable="true"
						:close-on-select="false"
					>
						<template slot="noOptions">
							<span>リストは空です。</span>
						</template>

						<template slot="noResult">
							<span>該当する結果が見つかりません。検索クエリを変更してください。</span>
						</template>
					</vMultiselect>
				</div>

				<template #modal-footer>
					<div class="d-flex w-100 justify-content-end align-items-end">
						<b-button :disabled="is_uploading_video" class="button-save" @click="handleUpdateVideoInfo()">
							<span>保存する</span>
						</b-button>
					</div>
				</template>
			</b-modal>

			<b-modal v-model="modal_assign_movie_on_dates" centered size="xl" no-close-on-backdrop>
				<template #modal-title>
					<span style="font-weight: bold;">配信予定</span>
				</template>

				<div class="d-flex flex-column w-100">
					<b-overlay
						:show="overlay_modal.show"
						:blur="overlay_modal.blur"
						:rounded="overlay_modal.sm"
						:variant="overlay_modal.variant"
						:opacity="overlay_modal.opacity"
						style="height: 100%"
					>
						<template #overlay>
							<div class="text-center">
								<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
								<p style="margin-top: 10px;">{{ $t('PLEASE_WAIT') }}</p>
							</div>
						</template>

						<div class="main-frame">
							<div class="calendar-nav-bar">
								<div class="d-flex flex-row icon-holder">
									<i class="far fa-angle-left prev-month-icon calendar-icon" @click="minus()" />
									<i class="far fa-angle-right next-month-icon calendar-icon" @click="plus()" />
								</div>

								<span class="current-date-text">{{ jp_current_date_format }}</span>

								<b-button
									class="show-calendar-button"
									@click="is_show_calendar = !is_show_calendar"
								>
									<i v-if="is_show_calendar" class="fad fa-expand" />
									<i v-else class="fad fa-compress-arrows-alt" />
								</b-button>
							</div>

							<div class="calendar-content">
								<Transition name="slide">
									<div v-if="is_show_calendar" class="calendar-general">
										<b-calendar
											:value="calendar"
											disabled
											:locale="locale"
											:label-help="''"
											:hide-header="true"
											:date-info-fn="dateClass"
											@context="onContext"
										/>
									</div>
								</Transition>

								<div class="d-flex flex-column" :style="is_show_calendar ? 'width: 80%;' : 'width: 100%;'">
									<div class="calendar-detail row align-items-start m-0">
										<div class="calendar-cell-top text-center font-weight-bolder col-2">
											<span>日</span>
										</div>

										<div class="calendar-cell-top text-center font-weight-bolder col-2">
											<span>月</span>
										</div>

										<div class="calendar-cell-top text-center font-weight-bolder col-2">
											<span>火</span>
										</div>

										<div class="calendar-cell-top text-center font-weight-bolder col-2">
											<span>水</span>
										</div>

										<div class="calendar-cell-top text-center font-weight-bolder col-2">
											<span>木</span>
										</div>

										<div class="calendar-cell-top text-center font-weight-bolder col-2">
											<span>金</span>
										</div>

										<div class="calendar-cell-top text-center font-weight-bolder col-2">
											<span>土</span>
										</div>
									</div>

									<div class="calendar-detail row align-items-start m-0">
										<template v-for="(date, index) in list_dates_in_month">
											<div
												v-if="date.id !== ''"
												:key="`date-exist-${index}`"
												class="calendar-cell col-2"
												@click="handleClickCalendarCell($event, date)"
											>
												<div :class="['cell-head', date?.is_grey ? 'grey-cell' : '']">
													<span>{{ date?.text_dow }}</span>
													<span>{{ date?.text }}</span>
												</div>

												<div class="cell-body">
													<div v-for="(movie, movieIndex) in date.movie_list" :key="movieIndex" class="d-flex flex-column w-100">
														<div v-if="movie?.time && movie?.movie_title" class="d-flex flex-row w-100 mb-2">
															<span class="movie-pill">
																{{ movie?.movie_title }}
															</span>

															<span class="movie-pill-delete" @click="handleDeleteMovie($event, index, movieIndex, date)">
																<i class="fas fa-times" />
															</span>
														</div>
													</div>
												</div>
											</div>

											<div v-else :key="`date-empty-${index}`" class="calendar-cell-empty col-2" />
										</template>
									</div>
								</div>
							</div>
						</div>

						<Transition name="slide">
							<div v-if="is_show_form" class="draggable-form" :style="{ top: `${position.y}px`, left: `${position.x}px` }">
								<div class="header" @mousedown="onMouseDown">
									<span>{{ convertDateToJapaneseFormat(form_data.date) }}</span>

									<div class="icon-holder" @click="handleCloseForm($event)">
										<i class="fas fa-times" />
									</div>
								</div>

								<div class="content">
									<div v-for="(item, movieIndex) in form_data.movie_list" :key="`list-item-${movieIndex}`" class="d-flex flex-column w-100">
										<div class="row m-0 w-100 justify-content-between align-items-center mb-3">
											<div class="col-2 p-0">
												<div class="d-flex flex-column align-items-center">
													<i class="far fa-camera-movie" />
													<span style="font-size: 12px;">Movie {{ movieIndex + 1 }}</span>
												</div>
											</div>

											<div class="col-10 p-0">
												<b-form-select
													v-model="item.movie_id"
													:options="form_data.list_movies"
													value-field="id"
													text-field="text"
													disabled-field="disabled"
													@change="handleChangeMovie($event, movieIndex)"
												/>
											</div>
										</div>

										<div class="row m-0 w-100 justify-content-between align-items-center mb-3">
											<div class="col-2 p-0">
												<div class="d-flex flex-column align-items-center">
													<i class="far fa-chess-clock" />
													<span style="font-size: 12px;">Time</span>
												</div>
											</div>

											<div class="col-10 p-0">
												<div class="d-flex flex-row w-100">
													<b-form-select
														v-model="item.hour"
														class="mr-3"
														:options="form_data.uploadHourOptions"
														value-field="id"
														text-field="text"
														disabled-field="disabled"
														@change="handleChangeHour($event, movieIndex)"
													/>

													<b-form-select
														v-model="item.minute"
														:options="form_data.uploadMinuteOptions"
														value-field="id"
														text-field="text"
														disabled-field="disabled"
														@change="handleChangeMinute($event, movieIndex)"
													/>
												</div>
											</div>
										</div>

										<div class="row m-0 w-100 justify-content-between align-items-center mb-3">
											<div class="col-2 p-0">
												<div class="d-flex flex-column align-items-center">
													<i class="far fa-clock" />
													<span style="font-size: 12px;">Type</span>
												</div>
											</div>

											<div class="col-10 p-0">
												<b-form-select
													v-model="item.assign_type"
													:options="assign_type_options"
													value-field="id"
													text-field="text"
													disabled-field="disabled"
												/>
											</div>
										</div>

										<div v-if="item.assign_type === 1" class="row m-0 w-100 justify-content-between align-items-center mb-3">
											<span>
												ムービーは以下に割り当てられます: {{ convertDateToJapaneseFormat(form_data.date) }}
											</span>
										</div>

										<div v-if="item.assign_type === 2" class="row m-0 w-100 justify-content-between align-items-center mb-3">
											<div class="col-2 p-0">
												<div class="d-flex flex-column align-items-center">
													<i class="fal fa-calendar-alt" />
													<span style="font-size: 12px;">From</span>
												</div>
											</div>

											<div class="col-10 p-0">
												<b-form-datepicker
													:id="`from-datepicker-${movieIndex}`"
													v-model="item.from"
													no-flip
													locale="ja"
													:reset-button="true"
													:max="handleGetMaxDate(null, item, movieIndex)"
													selected-variant="warning"
													label-reset-button="リセット"
													:min="item.min_assignable_date"
													:no-highlight-today="true"
													:initial-date="form_data.date"
													label-no-date-selected="日付が選択されていません"
													@change="handleGetMinDate($event, item, movieIndex)"
												/>
											</div>
										</div>

										<div v-if="item.assign_type === 2" class="row m-0 w-100 justify-content-between align-items-center mb-3">
											<div class="col-2 p-0">
												<div class="d-flex flex-column align-items-center">
													<i class="fal fa-calendar-alt" />
													<span style="font-size: 12px;">To</span>
												</div>
											</div>

											<div class="col-10 p-0">
												<b-form-datepicker
													:id="`to-datepicker-${movieIndex}`"
													v-model="item.to"
													no-flip
													locale="ja"
													:reset-button="true"
													:min="handleGetMinDate(null, item, movieIndex)"
													selected-variant="warning"
													label-reset-button="リセット"
													:max="item.max_assignable_date"
													:no-highlight-today="true"
													:initial-date="form_data.date"
													label-no-date-selected="日付が選択されていません"
													@change="handleGetMaxDate($event, item, movieIndex)"
												/>
											</div>
										</div>

										<div class="d-flex w-100 mb-3" style="border-top: 1px solid #D9D9D9;" />
									</div>

									<div v-if="form_data.movie_list.length <= 4" class="d-flex flex-row w-100">
										<b-button class="button-add-movie" @click="handleAddMovieOnDate()">
											<i class="far fa-plus" />
										</b-button>
									</div>

									<div v-if="form_data.movie_list.length > 0" class="row m-0 w-100 justify-content-between align-items-center">
										<div class="col-12 p-0 text-center">
											<div class="d-flex flex-row w-100 justify-content-between">
												<b-button variant="secondary" class="w-50 mt-3 mr-3" @click="handleRemoveMovieOnDate()">
													<i class="far fa-trash" />
												</b-button>

												<b-button :disabled="handleDisabledTempAssignButton()" style="background-color: #0f0448;" class="w-50 mt-3" @click="handleTemporaryAssignDate()">
													<i class="fal fa-calendar-check" />
												</b-button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</Transition>
					</b-overlay>
				</div>

				<template #modal-footer>
					<div class="d-flex w-100 justify-content-end align-items-end">
						<b-button class="button-save" @click="handleAssignMovieOnDates()">
							<span>保存する</span>
						</b-button>
					</div>
				</template>
			</b-modal>

			<b-modal v-model="modal_delete_confirm" centered size="md" no-close-on-backdrop @hide="handleCloseModalConfirmDelete()">
				<div class="d-flex flex-row justify-content-center">
					<span>この動画を削除してよろしいですか？</span>
				</div>

				<template #modal-footer>
					<div class="d-flex flex-row modal-confirm-delete">
						<b-button class="mr-3" @click="handleCloseModalConfirmDelete()">
							<span>いいえ</span>
						</b-button>

						<b-button @click="handleDeleteVideo()">
							<span>はい</span>
						</b-button>
					</div>
				</template>
			</b-modal>

			<b-modal v-model="modal_delivery_record" centered size="xl" no-close-on-backdrop>
				<template #modal-title>
					<span style="font-weight: bold;">配信記録</span>
				</template>

				<div class="d-flex flex-column w-100 table-delivery-record-holder">
					<b-table-simple id="table-delivery-record" bordered>
						<b-thead>
							<b-th class="table-delivery-record-th">日付</b-th>
							<b-th class="table-delivery-record-th">タイトル</b-th>
							<b-th class="table-delivery-record-th">閲覧数</b-th>
							<b-th class="table-delivery-record-th">データ出力</b-th>
						</b-thead>

						<b-tbody>
							<template v-if="delivery_record.length === 0">
								<b-tr>
									<b-td colspan="4" class="text-center">データなし</b-td>
								</b-tr>
							</template>

							<template v-else>
								<b-tr v-for="(item, index) in delivery_record" :key="index">
									<b-td class="table-delivey-record-td">{{ item['date'] }}</b-td>
									<b-td class="table-delivey-record-td">{{ item['movie_title'] }}</b-td>
									<b-td class="table-delivey-record-td">{{ item['total'] }}</b-td>
									<b-td class="table-delivey-record-td">
										<b-button class="button-export-csv" @click="handleExportFileCSV(item)">
											<i class="fas fa-file-csv" />
										</b-button>
									</b-td>
								</b-tr>
							</template>
						</b-tbody>
					</b-table-simple>
				</div>

				<template #modal-footer>
					<div class="d-flex w-100 justify-content-end align-items-end">
						<b-button class="button-save" @click="() => { modal_delivery_record = false; }">
							<span>戻る</span>
						</b-button>
					</div>
				</template>
			</b-modal>

			<b-modal v-model="modal_bulk_export" centered size="xl" no-close-on-backdrop @hide="handleCloseBulkExportModal()">
				<template #modal-title>
					<span style="font-weight: bold;">視聴者一括出力</span>
				</template>

				<div class="d-flex flex-column w-100">
					<div class="bulk-export-filter-section mb-3 p-3">
						<b-row class="mb-3">
							<b-col cols="12" md="6">
								<label class="font-weight-bold">タイトル検索</label>
								<b-form-input
									v-model="bulk_export_filter.title"
									placeholder="タイトルで検索..."
									class="mt-2"
								/>
							</b-col>

							<b-col cols="12" md="3">
								<label class="font-weight-bold">開始日付</label>
								<b-form-input
									v-model="bulk_export_filter.start_date"
									type="date"
									:max="bulk_export_filter.end_date || undefined"
									class="mt-2"
								/>
							</b-col>

							<b-col cols="12" md="3">
								<label class="font-weight-bold">終了日付</label>
								<b-form-input
									v-model="bulk_export_filter.end_date"
									type="date"
									:min="bulk_export_filter.start_date || undefined"
									class="mt-2"
								/>
							</b-col>
						</b-row>

						<b-row>
							<b-col cols="12" class="d-flex justify-content-between align-items-center">
								<span v-if="bulk_export_filter.start_date && bulk_export_filter.end_date" class="text-muted">
									期間: {{ bulk_export_filter.start_date }} ~ {{ bulk_export_filter.end_date }}
								</span>
								<span v-else class="text-muted">
									期間が指定されていません
								</span>
								<span class="selected-count-text">
									選択数: {{ bulk_export_selected_movie_ids.length }} / {{ filteredBulkExportMovies.length }}
								</span>
							</b-col>
						</b-row>
					</div>

					<div class="bulk-export-list-holder">
						<b-table-simple bordered>
							<b-thead>
								<b-tr>
									<b-th class="text-center bulk-export-th" style="width: 60px;">
										<b-form-checkbox
											v-model="bulk_export_select_all"
											@change="handleToggleSelectAll()"
										/>
									</b-th>
									<!-- <b-th class="bulk-export-th">日付</b-th> -->
									<b-th class="bulk-export-th">タイトル</b-th>
								</b-tr>
							</b-thead>

							<b-tbody>
								<template v-if="filteredBulkExportMovies.length === 0">
									<b-tr>
										<b-td colspan="4" class="text-center">データなし</b-td>
									</b-tr>
								</template>

								<template v-else>
									<b-tr v-for="(movie, index) in filteredBulkExportMovies" :key="index">
										<b-td class="text-center bulk-export-td">
											<b-form-checkbox
												v-if="movie.id"
												:checked="bulk_export_selected_movie_ids.includes(movie.id)"
												@change="handleToggleMovieSelection(movie.id)"
											/>
											<span v-else class="text-muted">-</span>
										</b-td>
										<b-td class="bulk-export-td">{{ movie.title || '-' }}</b-td>
									</b-tr>
								</template>
							</b-tbody>
						</b-table-simple>
					</div>
				</div>

				<template #modal-footer>
					<div class="d-flex w-100 justify-content-between align-items-end">
						<b-button variant="secondary" @click="handleCloseBulkExportModal()">
							<span>キャンセル</span>
						</b-button>

						<b-button
							class="button-save"
							:disabled="bulk_export_selected_movie_ids.length === 0 && (!bulk_export_filter.start_date || !bulk_export_filter.end_date)"
							@click="handleBulkExportMovies()"
						>
							<span v-if="bulk_export_selected_movie_ids.length > 0">出力 ({{ bulk_export_selected_movie_ids.length }})</span>
							<span v-else>出力</span>
						</b-button>
					</div>
				</template>
			</b-modal>
		</b-overlay>
	</div>
</template>

<script>
import 'vue2-datepicker/index.css';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';

import draggable from 'vuedraggable';
import vue2Dropzone from 'vue2-dropzone';
import vMultiselect from 'vue-multiselect';
import vButton from '@/components/atoms/vButton';
import vPagination from '@/components/atoms/vPagination';
import vHeaderPage from '@/components/atoms/vHeaderPage.vue';
import vHeaderFilter from '@/components/atoms/vHeaderFilter';

import { obj2Path } from '@/utils/obj2Path';
import { cleanObj } from '@/utils/handleObj';
import { MakeToast } from '@/utils/MakeToast';
import {
    postFile,
    postVideo,
    editVideo,
    deleteVideo,
    getVideoDetail,
    getMovieOnDates,
    changeVideoOrder,
    getDeliveryRecord,
    getListVideoPlayer,
    assignMovieOnDates,
    getListBulkExport,
    updateMovieLoopEnabled,
} from '@/api/modules/videoPlayer';

const url_api_list = {
    apiPostVideo: '/movies',
    apiEditVideo: '/movies',
    apiDeleteVideo: '/movies',
    apiGetListVideo: '/movies',
    apiGetVideoDetail: '/movies',
    apiPostFile: '/movies/upload-file',
    apiChangeOrder: '/movies/update-position',
    apiGetAssignMovieOnDates: '/movies/schedule',
    apiExportFileCSV: '/movies/dowload-user-watching',
    apiAssignMovieOnDates: '/movies/store-movie-schedule',
    apiGetDeliveryRecord: '/movies/show-user-watch-movie',
    apiGetListBulkExport: '/movies/all-watching-movie-list',
    apiDownloadDeliveryRecord: '/movies/download-all-watching-movie',
    apiUpdateLoopEnabled: '/movies',
};

export default {
    name: 'VideoPlayerIndex',
    components: {
        vButton,
        draggable,
        vHeaderPage,
        vPagination,
        vMultiselect,
        vHeaderFilter,
        vuedropzone: vue2Dropzone,
    },
    data() {
        return {
            overlay: {
                show: false,
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
                variant: 'light',
            },

            overlay_modal: {
                show: false,
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
                variant: 'light',
            },

            items: [],
            list_item_draggble: [],

            filter: {
                title: {
                    value: '',
                    status: false,
                },
                tag: {
                    value: [],
                    status: false,
                },
            },

            pagination: {
                per_page: 20,
                total_rows: 0,
                current_page: 1,
            },

            year_month_picker: this.$store.getters.yearMonthPickerDriverRecorder.date || new Date().toISOString().slice(0, 7),
            current_year: this.$store.getters.yearMonthPickerDriverRecorder.year || new Date().getFullYear(),
            current_month: this.$store.getters.yearMonthPickerDriverRecorder.month || new Date().getMonth() + 1,
            ja_locale: {
                months: [
                    '1月',
                    '2月',
                    '3月',
                    '4月',
                    '5月',
                    '6月',
                    '7月',
                    '8月',
                    '9月',
                    '10月',
                    '11月',
                    '12月',
                ],
            },

            min_year: 2020,
            max_year: 2070,

            modal_register_movie: false,
            modal_assign_movie_on_dates: false,

            title: '',
            description: '',

            video_file: null,
            video_file_id: null,

            video_length: '',

            thumbnail_file: null,
            thumbnail_file_id: null,

            video_source: '',
            video_name: '選択してください',
            is_uploading_video: false,
            thumbnail_url: '',

            tag: [],
            tag_options: [
                { value: 1, text: 'トラックの死角', disabled: false },
                { value: 2, text: 'トラックの特性', disabled: false },
                { value: 3, text: '運転マナー', disabled: false },
                { value: 4, text: '言動マナー', disabled: false },
                { value: 5, text: '防衛運転', disabled: false },
                { value: 6, text: '労働災害', disabled: false },
            ],

            options_per_page: [
                { value: 20, text: '20' },
                { value: 50, text: '50' },
                { value: 100, text: '100' },
                { value: 250, text: '250' },
                { value: 500, text: '500' },
            ],

            video_id: null,
            modal_delete_confirm: false,

            dragging: false,

            modal_edit_video: false,

            video_information: {
                id: null,

                title: '',
                content: '',

                file_id: null,
                thumbnail_file_id: null,

                video_source: '',
                video_name: '',

                thumbnail_url: '',
                thumbnail_name: '',

                video_file: null,
                thumbnail_file: null,

                video_length: '',
            },

            list_selected_tags: [],

            list_dates_in_month: [],

            calendar: '',
            locale: 'ja-JP',

            jp_current_date_format: '',

            is_show_form: false,
            position: { x: 0, y: 0 },
            offset: { x: 0, y: 0 },
            is_dragging: false,
            animation_frame: null,

            form_data: {
                date: '',
                movie_id: '',
                movie_title: '',
                text: '',
                text_dow: '',
                current_movie: null,
                list_movies: [
                    { id: null, text: '選択してください', disabled: false },
                ],
                from: null,
                to: null,
                upload_time_hours: 0,
                uploadHourOptions: [
                    { id: 0, text: '00', disabled: false },
                    ...Array.from({ length: 23 }, (v, i) => ({
                        id: i + 1,
                        text: (i + 1).toString().padStart(2, '0'),
                        disabled: false,
                    })),
                ],
                upload_time_minutes: 0,
                uploadMinuteOptions: [
                    { id: 0, text: '00', disabled: false },
                    { id: 15, text: '15', disabled: false },
                    { id: 30, text: '30', disabled: false },
                    { id: 45, text: '45', disabled: false },
                ],
                movie_list: [],
            },

            list_movies_assigned: [],
            min_assignable_date: '',
            max_assignable_date: '',

            assign_type: 1,
            assign_type_options: [
                { id: 1, text: '当日', disabled: false },
                { id: 2, text: 'カスタム範囲', disabled: false },
            ],

            is_show_calendar: false,

            dropzoneFileOptions: {
                method: 'POST',
                headers: { 'Authorization': this.$store.getters.token },
                url: `${window.origin}/api${url_api_list.apiPostFile}`,

                parallelChunkUploads: false,
                chunking: true,
                chunkSize: 10000000,

                maxFiles: 1,
                maxFilesize: 3 * 1024 * 1024 * 1024,
                uploadMultiple: false,

                acceptedFiles: 'video/mp4, video/quicktime',

                previewTemplate: this.template(),
            },

            modal_delivery_record: false,
            delivery_record: [],

            modal_bulk_export: false,
            bulk_export_movies: [],
            bulk_export_filter: {
                title: '',
                start_date: '',
                end_date: '',
            },
            bulk_export_selected_movie_ids: [],
            bulk_export_select_all: false,

            file: '',

            start_date: '',
            end_date: '',
            show_date_modal: false,
        };
    },
    computed: {
        filteredBulkExportMovies() {
            let filtered = [...this.bulk_export_movies];

            if (this.bulk_export_filter.title) {
                const searchTerm = this.bulk_export_filter.title.toLowerCase();
                filtered = filtered.filter(movie =>
                    movie.title && movie.title.toLowerCase().includes(searchTerm)
                );
            }

            return filtered;
        },
    },
    created() {
        this.handleGetListVideo();
        this.handleRenderJapaneseCurrentDate();
    },
    methods: {
        template() {
            return `<div></div>`;
        },
        onClickClearAll() {
            const FILER = {
                title: {
                    value: '',
                    status: false,
                },
                tag: {
                    value: [],
                    status: false,
                },
            };

            this.filter = FILER;
        },
        onClickApply() {
            this.handleGetListVideo();
        },
        handleChangeCurrentPage(value) {
            if (value) {
                this.pagination.current_page = value;
                this.handleGetListVideo();
            }
        },
        handleChangePerPage(value) {
            if (value) {
                this.pagination.per_page = value;
                this.handleGetListVideo();
            }
        },
        disabledDate(date) {
            return date.getFullYear() < this.min_year || date.getFullYear() > this.max_year;
        },
        async handleChangeInput(value) {
            const year = value.split('-')[0];
            const month = value.split('-')[1];

            this.year_month_picker = `${year}-${month}`;
            this.calendar = `${year}-${month}`;
            this.current_year = parseInt(year);
            this.current_month = parseInt(month);

            this.handleRenderJapaneseCurrentDate();
            await this.handleRenderCalendar(value);
            this.handleGetAssignMovieOnDates();
        },
        async handleGetListVideo() {
            this.video_id = null;

            this.items = [];
            this.list_item_draggble = [];
            this.form_data.list_movies = [
                { id: null, text: '選択してください', disabled: false },
            ];

            try {
                this.overlay.show = true;

                const listTagFilter = [];

                if (this.filter.tag.value.length) {
                    for (let i = 0; i < this.filter.tag.value.length; i++) {
                        const element = this.filter.tag.value[i];
                        listTagFilter.push(element.value);
                    }
                }

                let params = {
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    title: this.filter.title.status ? this.filter.title.value : '',
                    tag: this.filter.tag.status ? `[${listTagFilter.toString()}]` : null,
                };

                params = cleanObj(params);

                const url = `${url_api_list.apiGetListVideo}?${obj2Path(params)}`;

                const response = await getListVideoPlayer(url);

                if (response.code === 200) {
                    this.items = response.data.result;
                    this.pagination.total_rows = response.data.pagination.total_records;
                }
            } catch (error) {
                this.overlay.show = false;
                console.log(error);
            }

            this.overlay.show = false;
        },
        async handleToggleLoopEnabled(item) {
            const previousState = item.is_loop_enabled;

            try {
                this.overlay.show = true;

                const url = `${url_api_list.apiUpdateLoopEnabled}/${item.id}/loop-enabled`;

                const data = {
                    is_loop_enabled: item.is_loop_enabled,
                };

                const response = await updateMovieLoopEnabled(url, data);

                if (response.code === 200) {
                    MakeToast({
                        variant: 'success',
                        title: '成功',
                        content: 'ループ配信設定を更新しました',
                    });
                }
            } catch (error) {
                console.log(error);

                item.is_loop_enabled = previousState;

                MakeToast({
                    variant: 'danger',
                    title: 'エラー',
                    content: 'ループ配信設定の更新に失敗しました',
                });

                await this.handleGetListVideo();
            }

            this.overlay.show = false;
        },
        handleClickRegisterButton() {
            this.title = '';
            this.description = '';
            this.video_file = null;
            this.video_file_id = null;
            this.video_length = '';
            this.thumbnail_file = null;
            this.thumbnail_file_id = null;
            this.video_source = '';
            this.thumbnail_url = '';
            this.tag = [];

            this.modal_register_movie = true;
        },
        async handleClickSaveButton() {
            try {
                this.overlay.show = true;

                const url = `${url_api_list.apiPostVideo}`;

                const listTag = [];

                if (this.tag.length) {
                    for (let i = 0; i < this.tag.length; i++) {
                        const element = this.tag[i];
                        listTag.push(element.value);
                    }
                }

                const data = {
                    title: this.title,
                    content: this.description,
                    file_id: this.video_file_id,
                    file_length: this.video_length,
                    thumbnail_file_id: this.thumbnail_file_id,
                    list_tags: listTag,
                };

                const response = await postVideo(url, data);

                if (response.code === 200) {
                    this.modal_register_movie = false;
                    this.handleGetListVideo();
                }
            } catch (error) {
                this.overlay.show = false;
                console.log(error);
            }

            this.overlay.show = false;
        },
        async handlePostFileToServer(file, type, is_edit) {
            const VIDEO = 0;
            const IMAGE = 1;

            try {
                const url = `${url_api_list.apiPostFile}`;

                if (file) {
                    const formData = new FormData();

                    formData.append('file', file);

                    const response = await postFile(url, formData);

                    if (response) {
                        if (type === VIDEO) {
                            if (is_edit) {
                                this.video_information.file_id = response.id;
                            } else {
                                this.video_file_id = response.id;
                            }
                        } else if (type === IMAGE) {
                            if (is_edit) {
                                this.video_information.thumbnail_file_id = response.id;
                            } else {
                                this.thumbnail_file_id = response.id;
                            }
                        }
                    }
                }
            } catch (error) {
                console.log(error);
            }
        },
        async handleClickAssignMoviesButton() {
            this.modal_assign_movie_on_dates = true;

            if (this.modal_assign_movie_on_dates) {
                await this.handleRenderCalendar();
                this.handleGetAssignMovieOnDates();
            }
        },
        handleSelectVideoFile(event, is_edit) {
            const file = event;

            const fileName = file['upload']['filename'];

            if (is_edit) {
                this.video_information.video_name = fileName;
            } else {
                this.video_name = fileName;
            }

            const reader = new FileReader();

            reader.onload = (e) => {
                if (is_edit) {
                    this.video_information.video_source = e.target.result;
                } else {
                    this.video_source = e.target.result;
                }

                const video = document.createElement('video');

                video.preload = 'metadata';

                video.onloadedmetadata = () => {
                    const duration = video.duration;
                    const formattedDuration = this.formatDuration(duration);

                    if (is_edit) {
                        this.video_information.file_length = formattedDuration;
                    } else {
                        this.video_length = formattedDuration;
                    }
                };

                video.src = reader.result;
            };

            reader.readAsDataURL(file);
        },
        formatDuration(duration) {
            const hours = Math.floor(duration / 3600);
            const minutes = Math.floor((duration % 3600) / 60);
            const seconds = Math.round(duration % 60);

            let formattedDuration = '';

            if (hours > 0) {
                formattedDuration += `${this.padZero(hours)}:${this.padZero(minutes)}:${this.padZero(seconds)}`;
            } else if (minutes > 0) {
                formattedDuration += `${this.padZero(minutes)}:${this.padZero(seconds)}`;
            } else {
                formattedDuration += `00:${this.padZero(seconds)}`;
            }

            return formattedDuration;
        },
        padZero(num) {
            if (num < 10) {
                return num.toString().padStart(2, '0');
            }

            return num.toString();
        },
        async handleSelectImageFile(event, is_edit) {
            const file = event;

            if (file) {
                if (!['image/png', 'image/jpg', 'image/jpeg'].includes(file?.type)) {
                    if (is_edit) {
                        await this.$refs.thumbnailFileEdit.reset();
                    } else {
                        await this.$refs.thumbnailFileRegister.reset();
                    }

                    MakeToast({
                        variant: 'warning',
                        title: this.$t('WARNING'),
                        content: '画像の種類はPNGおよびJPGのみ許可されています。',
                    });
                } else {
                    if (is_edit) {
                        await this.handlePostFileToServer(file, 1, true);
                    } else {
                        await this.handlePostFileToServer(file, 1, false);
                    }

                    const reader = new FileReader();

                    reader.onload = (e) => {
                        if (is_edit) {
                            this.video_information.thumbnail_url = e.target.result;
                        } else {
                            this.thumbnail_url = e.target.result;
                        }
                    };

                    reader.readAsDataURL(file);
                }
            }
        },
        returnTextFromOption(value) {
            let text = '';

            for (let i = 0; i < this.tag_options.length; i++) {
                const element = this.tag_options[i];

                if (element.value === value) {
                    text = element.text;
                    break;
                }
            }

            return text;
        },
        handleOpenModalConfirmDelete(video_id) {
            this.video_id = video_id;
            this.modal_delete_confirm = true;
        },
        handleCloseModalConfirmDelete() {
            this.video_id = null;
            this.modal_delete_confirm = false;
        },
        async handleDeleteVideo() {
            try {
                this.overlay.show = true;

                const url = `${url_api_list.apiDeleteVideo}/${this.video_id}`;

                const response = await deleteVideo(url);

                if (response.code === 200) {
                    await this.handleGetListVideo();
                    this.modal_delete_confirm = false;
                }

                console.log(response);
            } catch (error) {
                console.log(error);
                this.overlay.show = false;
            }

            this.overlay.show = false;
        },
        dragChanged() {
            const newListOne = [...this.items].map((item, index) => {
                const newSort = index;

                item.hasChanged = item.sortOrder !== newSort;

                if (item.hasChanged) {
                    item.sortOrder = newSort;
                }

                return item.id;
            });

            this.handleSaveOrder(newListOne);
        },
        async handleSaveOrder(list_changed_movie) {
            try {
                this.overlay.show = true;

                const url = `${url_api_list.apiChangeOrder}`;

                const data = {
                    list_position: list_changed_movie,
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                };

                const response = await changeVideoOrder(url, data);

                console.log(response);
            } catch (error) {
                console.log(error);
                this.overlay.show = false;
            }

            this.overlay.show = false;
        },
        async handleOpenModalEditVideo(video_id) {
            this.video_id = video_id;

            await this.handleGetVideoDetail(video_id);

            this.modal_edit_video = true;
        },
        async handleGetVideoDetail() {
            this.list_selected_tags = [];

            try {
                this.overlay.show = true;

                const url = `${url_api_list.apiGetVideoDetail}/${this.video_id}`;

                const response = await getVideoDetail(url);

                if (response.code === 200) {
                    const data = response.data;

                    if (data.tag !== null) {
                        for (let i = 0; i < data.tag.length; i++) {
                            for (let j = 0; j < this.tag_options.length; j++) {
                                if (data.tag[i] === this.tag_options[j].value) {
                                    this.list_selected_tags.push(this.tag_options[j]);
                                    break;
                                }
                            }
                        }
                    }

                    this.video_information = {
                        id: data?.id,

                        title: data?.title,
                        content: data?.content,

                        file_id: data?.movie_file?.id,
                        thumbnail_file_id: data?.thumbnail?.id,

                        video_source: data?.movie_file?.file_url,
                        video_name: data?.movie_file?.file_name,

                        thumbnail_url: data?.thumbnail?.file_url,
                        thumbnail_name: data?.thumbnail?.file_name || '選択してください',

                        video_length: data?.file_length,
                    };
                }
            } catch (error) {
                console.log(error);
                this.overlay.show = false;
            }

            this.overlay.show = false;
        },
        async handleUpdateVideoInfo() {
            try {
                this.overlay.show = true;

                const url = `${url_api_list.apiEditVideo}/${this.video_id}`;

                const listTag = [];

                if (this.list_selected_tags.length) {
                    for (let i = 0; i < this.list_selected_tags.length; i++) {
                        const element = this.list_selected_tags[i];
                        listTag.push(element.value);
                    }
                }

                const data = {
                    title: this.video_information.title,
                    content: this.video_information.content,
                    file_id: this.video_information.file_id,
                    file_length: this.video_information.video_length,
                    list_tags: listTag,
                };

                if (this.video_information.thumbnail_file_id) {
                    data.thumbnail_file_id = this.video_information.thumbnail_file_id;
                }

                const response = await editVideo(url, data);

                if (response.code === 200) {
                    await this.handleGetListVideo();
                    this.modal_edit_video = false;
                }
            } catch (error) {
                console.log(error);
                this.overlay.show = false;
            }

            this.overlay.show = false;
        },
        handleCloseModalRegister() {
            this.video_information = {
                title: '',
                content: '',

                video_file: null,
                video_file_id: null,
                video_length: '',
                video_source: '',
                video_name: '',

                thumbnail_file: null,
                thumbnail_file_id: null,
                thumbnail_url: '',
                thumbnail_name: '',
            };

            this.video_name = '';

            this.list_selected_tags = [];

            this.modal_register_movie = false;
        },
        handleCloseModalEditVideo() {
            this.video_information = {
                title: '',
                content: '',

                video_file: null,
                video_file_id: null,
                video_length: '',
                video_source: '',
                video_name: '',

                thumbnail_file: null,
                thumbnail_file_id: null,
                thumbnail_url: '',
                thumbnail_name: '',
            };

            this.video_name = '';

            this.list_selected_tags = [];

            this.modal_edit_video = false;
        },
        async handleAssignMovieOnDates() {
            try {
                this.overlay.show = true;

                const url = `${url_api_list.apiAssignMovieOnDates}`;

                const listMovieInMonth = [];
                const listMovieInMonthWithDates = [];
                const listDatesHaveMovieInMonth = [];

                for (let i = 0; i < this.list_dates_in_month.length; i++) {
                    const element = this.list_dates_in_month[i];

                    const movieList = [...this.list_dates_in_month[i].movie_list];

                    for (let j = 0; j < movieList.length; j++) {
                        if (movieList[j].movie_id !== null) {
                            listDatesHaveMovieInMonth.push(`${this.padZero(element.id)}`);
                        }

                        if (movieList[j].movie_id !== null && !listMovieInMonth.includes(movieList[j].movie_id)) {
                            listMovieInMonth.push(movieList[j].movie_id);
                        }
                    }
                }

                for (let i = 0; i < listDatesHaveMovieInMonth.length; i++) {
                    const date = listDatesHaveMovieInMonth[i];

                    listMovieInMonthWithDates.push(
                        {
                            date: date,
                            data: [],
                        }
                    );

                    for (let j = 0; j < this.list_dates_in_month.length; j++) {
                        const element = this.list_dates_in_month[j];

                        if (element.id === date) {
                            element.movie_list.forEach((movie) => {
                                if (movie.movie_id) {
                                    listMovieInMonthWithDates[i].data.push(
                                        {
                                            movie_id: movie.movie_id,
                                            time: movie.time,
                                            assign_type: movie.from && movie.to ? 2 : 1,
                                            from: movie.from || '',
                                            to: movie.to || '',
                                        }
                                    );
                                }
                            });
                        }
                    }
                }

                const data = {
                    list_movie_viewer: listMovieInMonthWithDates,
                    list_date: listDatesHaveMovieInMonth,
                    range: [this.min_assignable_date, this.max_assignable_date],
                };

                const response = await assignMovieOnDates(url, data);

                if (response.code === 200) {
                    await this.handleRenderCalendar();
                    await this.handleGetListVideo();

                    this.modal_assign_movie_on_dates = false;

                    MakeToast({
                        variant: 'success',
                        title: this.$t('SUCCESS'),
                        content: 'ムービーの配信予定が正常に割り当てられました。',
                    });
                }
            } catch (error) {
                console.log(error);
                this.overlay.show = false;
            }

            this.overlay.show = false;
        },
        handleFormatMonthDay(number) {
            if (typeof number === 'string') {
                number = parseInt(number);
            }

            return number < 10 ? `0${number}` : number;
        },
        handleRenderCalendar() {
            this.list_dates_in_month = [];

            const date = new Date(this.year_month_picker);
            const year = date.getFullYear();
            const month = date.getMonth();

            const lastDay = new Date(year, month + 1, 0).getDate();
            const lastMonthLastDay = new Date(year, month, 0).getDate();

            const daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];

            const previousMonth = month === 0 ? 11 : month - 1;
            const previousYear = month === 0 ? year - 1 : year;

            this.min_assignable_date = `${previousYear}-${this.handleFormatMonthDay(previousMonth + 1)}-${this.handleFormatMonthDay(lastMonthLastDay - 5)}`;

            for (let i = lastMonthLastDay - 5; i <= lastMonthLastDay; i++) {
                const date = new Date(previousYear, previousMonth, i);
                const dayOfWeek = daysOfWeek[date.getDay()];

                this.list_dates_in_month.push(
                    {
                        text: `${i}日`, text_dow: dayOfWeek,
                        id: `${previousYear}-${this.handleFormatMonthDay(previousMonth + 1)}-${this.handleFormatMonthDay(i)}`,
                        movie_list: [],
                        is_grey: true,
                    }
                );
            }

            for (let i = 1; i <= lastDay; i++) {
                const date = new Date(year, month, i);
                const dayOfWeek = daysOfWeek[date.getDay()];

                this.list_dates_in_month.push(
                    {
                        text: `${i}日`,
                        text_dow: dayOfWeek,
                        id: `${year}-${this.handleFormatMonthDay(month + 1)}-${this.handleFormatMonthDay(i)}`,
                        movie_list: [],
                        is_grey: false,
                    }
                );
            }

            const nextMonth = month === 11 ? 0 : month + 1;
            const nextYear = month === 11 ? year + 1 : year;

            this.max_assignable_date = `${nextYear}-${this.handleFormatMonthDay(nextMonth + 1)}-${this.handleFormatMonthDay(6)}`;

            for (let i = 1; i <= 6; i++) {
                const date = new Date(nextYear, nextMonth, i);
                const dayOfWeek = daysOfWeek[date.getDay()];

                this.list_dates_in_month.push(
                    {
                        text: `${i}日`,
                        text_dow: dayOfWeek,
                        id: `${nextYear}-${this.handleFormatMonthDay(nextMonth + 1)}-${this.handleFormatMonthDay(i)}`,
                        movie_list: [],
                        is_grey: true,
                    }
                );
            }

            const firstDayInCalendarText = this.list_dates_in_month[0]['text_dow'];
            const indexAdjustment = daysOfWeek.indexOf(firstDayInCalendarText);

            for (let i = 0; i < indexAdjustment; i++) {
                this.list_dates_in_month.unshift(
                    {
                        text: '',
                        text_dow: '',
                        id: '',
                        movie_list: [],
                        is_grey: true,
                    }
                );
            }
        },
        formatYearMonth(year, month) {
            if (month >= 1 && month <= 9) {
                month = '0' + month;
            }

            return `${year}-${month}`;
        },
        async minus() {
            this.current_month = parseInt(this.current_month);
            this.current_year = parseInt(this.current_year);

            if (this.current_month > 1) {
                this.current_month = this.current_month - 1;
            } else if (this.current_month === 1) {
                this.current_month = 12;
                this.current_year = this.current_year - 1;
            }

            this.year_month_picker = this.formatYearMonth(this.current_year, this.current_month);
            this.calendar = `${this.year_month_picker}`;

            this.handleRenderJapaneseCurrentDate();
            await this.handleRenderCalendar();
            this.handleGetAssignMovieOnDates();
        },
        async plus() {
            this.current_month = parseInt(this.current_month);
            this.current_year = parseInt(this.current_year);

            if (this.current_month < 12) {
                this.current_month = this.current_month + 1;
            } else if (this.current_month === 12) {
                this.current_month = 1;
                this.current_year = this.current_year + 1;
            }

            this.year_month_picker = this.formatYearMonth(this.current_year, this.current_month);
            this.calendar = `${this.year_month_picker}`;

            this.handleRenderJapaneseCurrentDate();
            await this.handleRenderCalendar();
            this.handleGetAssignMovieOnDates();
        },
        truncateText(text, maxLength) {
            if (text) {
                if (text.length <= maxLength) {
                    return text;
                } else {
                    return text.slice(0, maxLength) + '...';
                }
            }
        },
        handleRemoveMovieOnDate() {
            const date = this.form_data.date;

            const date_index = this.list_dates_in_month.findIndex(item => item.id === date);

            if (date_index !== -1) {
                this.list_dates_in_month[date_index].movie_list = [];
            }

            const index = this.list_movies_assigned.findIndex(item => item === date);

            if (index !== -1) {
                this.dateClass();
                this.list_movies_assigned.splice(index, 1);
            }

            this.form_data.date = null;
            this.form_data.movie_list = [];
            this.is_show_form = false;
        },
        async handleGetAssignMovieOnDates() {
            try {
                this.overlay_modal.show = true;

                const params = {
                    date: this.year_month_picker,
                };

                const url = `${url_api_list.apiGetAssignMovieOnDates}?${obj2Path(params)}`;

                const response = await getMovieOnDates(url);

                if (response.code === 200) {
                    const DATA = response.data.data;

                    DATA.forEach((element) => {
                        if (element['list_movie'].length > 0) {
                            this.list_movies_assigned.push(element['date']);
                        }
                    });

                    const listTitles = response.data.list_title;

                    if (listTitles.length > 0) {
                        this.list_item_draggble = listTitles;
                        this.form_data.list_movies = [
                            { id: null, text: '選択してください', disabled: false },
                        ];

                        listTitles.forEach(item => {
                            this.form_data.list_movies.push(
                                { id: item['id'], text: item['title'], disabled: false }
                            );
                        });
                    }

                    for (let i = 0; i < DATA.length; i++) {
                        const element = DATA[i];

                        for (let j = 0; j < this.list_dates_in_month.length; j++) {
                            if (element['date'] === this.list_dates_in_month[j]['id']) {
                                this.list_dates_in_month[j].movie_list = element['list_movie'];
                            }
                        }
                    }

                    this.overlay_modal.show = false;
                }
            } catch (error) {
                this.overlay_modal.show = false;
                console.log(error);
            }

            this.overlay.show = false;
        },
        onContext(ctx) {
            this.context = ctx;
        },
        handleClickCalendarCell(event, cell_data) {
            if (this.is_show_form) {
                this.is_show_form = false;
                this.form_data.date = null;
                this.form_data.movie_list = [];
            } else {
                const targetDiv = event.currentTarget;
                const offset = targetDiv.getBoundingClientRect();

                const modalOffset = document.getElementsByClassName('modal')[0];
                const scrollTop = modalOffset.scrollTop;

                if (offset.x <= 360.75) {
                    this.position = {
                        x: offset.left + 120,
                        y: offset.top + scrollTop - 150,
                    };
                } else {
                    this.position = {
                        x: offset.left - 545,
                        y: offset.top + scrollTop - 150,
                    };
                }

                this.form_data.date = cell_data.id;
                this.form_data.text = cell_data.text;
                this.form_data.text_dow = cell_data.text_dow;
                this.form_data.movie_list = cell_data.movie_list;

                const list_selected_movies = [];

                for (let i = 0; i < this.form_data.list_movies.length; i++) {
                    for (let j = 0; j < this.form_data.movie_list.length; j++) {
                        if (this.form_data.movie_list[j].movie_id && !list_selected_movies.includes(this.form_data.movie_list[j].movie_id)) {
                            list_selected_movies.push(this.form_data.movie_list[j].movie_id);
                        }

                        if (list_selected_movies.includes(this.form_data.list_movies[i].id) && this.form_data.list_movies[i].id !== null) {
                            this.form_data.list_movies[i].disabled = true;
                        } else {
                            this.form_data.list_movies[i].disabled = false;
                        }
                    }
                }

                this.is_show_form = true;
            }
        },
        handleCloseForm(event) {
            this.is_show_form = false;
            event.stopPropagation();
        },
        onMouseDown(event) {
            this.is_dragging = true;

            this.offset = {
                x: event.clientX - this.position.x,
                y: event.clientY - this.position.y,
            };

            document.addEventListener('mousemove', this.onMouseMove);
            document.addEventListener('mouseup', this.onMouseUp);
        },
        onMouseMove(event) {
            if (this.is_dragging) {
                if (this.animation_frame) {
                    cancelAnimationFrame(this.animation_frame);
                }

                this.animation_frame = requestAnimationFrame(() => {
                    this.position = {
                        x: event.clientX - this.offset.x,
                        y: event.clientY - this.offset.y,
                    };
                });
            }
        },
        onMouseUp() {
            this.is_dragging = false;

            document.removeEventListener('mousemove', this.onMouseMove);
            document.removeEventListener('mouseup', this.onMouseUp);

            if (this.animation_frame) {
                cancelAnimationFrame(this.animation_frame);
            }
        },
        handleRenderJapaneseCurrentDate() {
            this.jp_current_date_format = `${this.current_year}年${this.handleFormatMonthDay(this.current_month)}月`;
        },
        dateClass(ymd) {
            if (this.list_movies_assigned.includes(ymd)) {
                return 'table-warning';
            } else {
                return '';
            }
        },
        convertDateToJapaneseFormat(dateString) {
            const [year, month, day] = dateString.split('-');

            const japaneseFormattedDate = `${year}年${month}月${day}日`;

            return japaneseFormattedDate;
        },
        handleTemporaryAssignDate() {
            const date = this.form_data.date;

            this.form_data.movie_list.forEach((movie, movieIndex) => {
                if (movie.assign_type === 1) {
                    this.list_movies_assigned.push(date);

                    for (let i = 0; i < this.list_dates_in_month.length; i++) {
                        if (this.list_dates_in_month[i].id === date) {
                            this.list_dates_in_month[i].movie_list = [...this.form_data.movie_list];

                            this.list_dates_in_month[i].movie_list.forEach((movie) => {
                                if (movie.movie_id && movie.movie_title) {
                                    movie.time = `${this.padZero(movie.hour)}:${this.padZero(movie.minute)}`;
                                }
                            });
                        }
                    }
                } else {
                    const from = movie.from;
                    const to = movie.to;

                    if (!from || !to) {
                        MakeToast({
                            variant: 'warning',
                            title: this.$t('WARNING'),
                            content: '日付を選択してください。',
                        });
                        return;
                    }

                    const fromIndex = this.list_dates_in_month.findIndex((date) => date.id === from);
                    const toIndex = this.list_dates_in_month.findIndex((date) => date.id === to);

                    for (let i = fromIndex; i <= toIndex; i++) {
                        const data = {
                            ...movie,
                            time: `${this.padZero(movie.hour)}:${this.padZero(movie.minute)}`,
                        };

                        if (this.list_dates_in_month[i].movie_list.length > 5) {
                            MakeToast({
                                variant: 'warning',
                                title: this.$t('WARNING'),
                                content: '1日に観られる映画の最大本数は5本です。',
                            });
                        } else {
                            if (this.list_dates_in_month[i].movie_list[movieIndex]) {
                                this.list_dates_in_month[i].movie_list[movieIndex] = { ...data };
                            } else {
                                this.list_dates_in_month[i].movie_list.push({ ...data });
                            }
                        }
                    }
                }
            });

            this.is_show_form = false;
        },
        handleGetMinDate(event, item, index) {
            if (event) {
                this.form_data.movie_list[index].from = event;
            }

            if (item.from) {
                return item.from;
            } else {
                return item.min_assignable_date;
            }
        },
        handleGetMaxDate(event, item, index) {
            if (event) {
                this.form_data.movie_list[index].to = event;
            }

            if (item.to) {
                return item.to;
            } else {
                return item.max_assignable_date;
            }
        },
        afterAdded(file, type) {
            const maxFileSize = 3 * 1024 * 1024 * 1024;

            if (file.size > maxFileSize) {
                return;
            } else {
                this.is_uploading_video = true;
                this.video_information.video_source = '';

                if (type === 1) {
                    this.handleSelectVideoFile(file, false);
                } else {
                    this.handleSelectVideoFile(file, true);
                }
            }
        },
        afterComplete(response, type) {
            const maxFileSize = 3 * 1024 * 1024 * 1024;

            if (response.size > maxFileSize) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: '3GB以下のファイルのみアップロードが可能です',
                });
            } else {
                if (response.xhr.status === 201) {
                    const DATA = JSON.parse(response.xhr.response);

                    if (DATA) {
                        this.is_uploading_video = false;

                        if (type === 1) {
                            this.video_file_id = DATA.id;
                        } else {
                            this.video_information.file_id = DATA.id;
                        }
                    }
                }
            }
        },
        handleDisabledTempAssignButton() {
            let result = false;

            if (this.assign_type === 2) {
                if (!this.form_data.from || !this.form_data.to) {
                    result = true;
                } else {
                    result = false;
                }
            }

            return result;
        },
        handleDeleteThumbnail(type) {
            if (type === 2) {
                this.video_information.thumbnail_file = null;
                this.video_information.thumbnail_name = '選択してください';
                this.video_information.thumbnail_url = '';
                this.video_information.thumbnail_file_id = null;
            } else {
                this.thumbnail_file = null;
                this.thumbnail_url = '';
                this.thumbnail_file_id = null;
            }
        },
        async handleShowDeliveryRecord() {
            await this.handleGetDeliveryRecord();
            this.modal_delivery_record = true;
        },
        async handleGetDeliveryRecord() {
            this.delivery_record = [];

            try {
                this.overlay.show = true;

                const date = new Date();
                const currentData = date.getDate();

                let params = {
                    date: `${this.year_month_picker}-${currentData}`,
                };

                params = cleanObj(params);

                const url = `${url_api_list.apiGetDeliveryRecord}?${obj2Path(params)}`;

                const response = await getDeliveryRecord(url);

                if (response.code === 200) {
                    const data = response.data;

                    data.forEach((item) => {
                        this.delivery_record.push({
                            date: item['date'],
                            movie_id: item['movie']?.id,
                            movie_title: item['movie']?.title,
                            total: item['total'],
                            from: item['from'],
                            to: item['to'],
                        });
                    });
                }

                this.overlay.show = false;
            } catch (error) {
                console.log(error);
                this.overlay.show = false;
            }
        },
        async handleExportFileCSV(item) {
            const movieID = item.movie_id;
            const movieName = item.movie_title;
            const movieDate = item.date;
            const movieDateFrom = item.from;
            const movieDateTo = item.to;

            if (movieID) {
                try {
                    let params = {
                        movie_id: movieID,
                    };

                    if (movieDateFrom && movieDateTo) {
                        params['from'] = movieDateFrom;
                        params['to'] = movieDateTo;
                    } else {
                        params['date'] = movieDate;
                    }

                    params = cleanObj(params);

                    const url = `/api${url_api_list.apiExportFileCSV}?${obj2Path(params)}`;

                    await fetch(url, {
                        headers: {
                            'Accept-Language': this.$store.getters.language,
                            'Authorization': this.$store.getters.token,
                            'accept': 'application/json',
                        },
                    }).then(async(res) => {
                        let filename = `${movieName}_視聴結果.csv`;
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
                        .catch((error) => {
                            console.log(error);

                            this.$toast.danger({
                                content: this.$t('TOAST_HAVE_ERROR'),
                            });
                        });

                    this.file = '';
                } catch (error) {
                    console.log(error);
                }
            }
        },
        handleChangeMovie(movie_id, index) {
            const movieIndex = this.form_data.list_movies.findIndex(item => item.id === movie_id);

            const list_selected_movies = [];

            for (let i = 0; i < this.form_data.list_movies.length; i++) {
                for (let j = 0; j < this.form_data.movie_list.length; j++) {
                    if (this.form_data.movie_list[j].movie_id && !list_selected_movies.includes(this.form_data.movie_list[j].movie_id)) {
                        list_selected_movies.push(this.form_data.movie_list[j].movie_id);
                    }

                    if (list_selected_movies.includes(this.form_data.list_movies[i].id) && this.form_data.list_movies[i].id !== null) {
                        this.form_data.list_movies[i].disabled = true;
                    } else {
                        this.form_data.list_movies[i].disabled = false;
                    }
                }
            }

            if (movieIndex !== -1) {
                this.form_data.movie_list[index].movie_title = this.form_data.list_movies[movieIndex].text;
            }
        },
        handleChangeHour(event, index) {
            this.form_data.movie_list[index].hour = event;
        },
        handleChangeMinute(event, index) {
            this.form_data.movie_list[index].minute = event;
        },
        handleAddMovieOnDate() {
            const len = this.form_data.movie_list.length;

            const date = new Date(this.year_month_picker);
            const year = date.getFullYear();
            const month = date.getMonth();

            const lastMonthLastDay = new Date(year, month, 0).getDate();

            const previousMonth = month === 0 ? 11 : month - 1;
            const previousYear = month === 0 ? year - 1 : year;

            const nextMonth = month === 11 ? 0 : month + 1;
            const nextYear = month === 11 ? year + 1 : year;

            const minAssignableDate = `${previousYear}-${this.handleFormatMonthDay(previousMonth + 1)}-${this.handleFormatMonthDay(lastMonthLastDay - 5)}`;
            const maxAssignableDate = `${nextYear}-${this.handleFormatMonthDay(nextMonth + 1)}-${this.handleFormatMonthDay(6)}`;

            if (len <= 4) {
                this.form_data.movie_list.push(
                    {
                        movie_id: null,
                        movie_title: '',
                        time: '',
                        hour: 0,
                        minute: 0,
                        assign_type: 1,
                        from: '',
                        to: '',
                        min_assignable_date: minAssignableDate,
                        max_assignable_date: maxAssignableDate,
                    }
                );
            }
        },
        handleDeleteMovie(event, index, movie_index, cell_data) {
            event.stopPropagation();

            if (this.is_show_form) {
                this.is_show_form = false;
            }

            this.form_data.movie_list = cell_data.movie_list;

            this.form_data.list_movies.forEach((item) => {
                if (item.id === this.form_data.movie_list[movie_index].movie_id) {
                    item.disabled = false;
                }
            });

            this.list_dates_in_month[index].movie_list.splice(movie_index, 1);
        },
        async handleDownloadDeliveryRecord() {
            await this.handleShowBulkExportModal();
        },
        async handleShowBulkExportModal() {
            try {
                this.overlay.show = true;

                const url = `${url_api_list.apiGetListBulkExport}`;
                const response = await getListBulkExport(url);

                if (response.code === 200) {
                    this.bulk_export_movies = response.data || [];
                } else {
                    this.bulk_export_movies = [];
                }

                this.modal_bulk_export = true;
            } catch (error) {
                console.error('[handleShowBulkExportModal] ===>', error);
                this.bulk_export_movies = [];
            } finally {
                this.overlay.show = false;
            }
        },
        handleCloseBulkExportModal() {
            this.modal_bulk_export = false;
            this.bulk_export_selected_movie_ids = [];
            this.bulk_export_select_all = false;
            this.bulk_export_filter = {
                title: '',
                start_date: '',
                end_date: '',
            };
        },
        handleToggleSelectAll() {
            if (this.bulk_export_select_all) {
                this.bulk_export_selected_movie_ids = this.filteredBulkExportMovies
                    .filter(movie => movie.id)
                    .map(movie => movie.id);
            } else {
                this.bulk_export_selected_movie_ids = [];
            }
        },
        handleToggleMovieSelection(movieId) {
            if (!movieId) {
                console.warn('[handleToggleMovieSelection] Invalid movieId:', { movieId });
                return;
            }

            const index = this.bulk_export_selected_movie_ids.indexOf(movieId);

            if (index > -1) {
                this.bulk_export_selected_movie_ids.splice(index, 1);
            } else {
                this.bulk_export_selected_movie_ids.push(movieId);
            }

            this.bulk_export_select_all = this.bulk_export_selected_movie_ids.length === this.filteredBulkExportMovies.length;
        },
        async handleBulkExportMovies() {
            if (this.bulk_export_selected_movie_ids.length === 0 && (!this.bulk_export_filter.start_date || !this.bulk_export_filter.end_date)) {
                MakeToast({
                    variant: 'warning',
                    title: this.$t('WARNING'),
                    content: 'ムービーを選択するか、開始日付と終了日付を入力してください。',
                });
                return;
            }

            if (this.bulk_export_filter.start_date && this.bulk_export_filter.end_date) {
                if (this.bulk_export_filter.start_date > this.bulk_export_filter.end_date) {
                    MakeToast({
                        variant: 'warning',
                        title: this.$t('WARNING'),
                        content: '開始日付は終了日付より前でなければなりません。',
                    });
                    return;
                }
            }

            let movieIds = [];

            if (this.bulk_export_selected_movie_ids.length > 0) {
                movieIds = this.bulk_export_selected_movie_ids.filter(id => !isNaN(id));
            }

            try {
                this.overlay.show = true;

                const params = {};

                if (movieIds.length > 0) {
                    params.movie_ids = movieIds;
                }

                if (this.bulk_export_filter.title) {
                    params.title = this.bulk_export_filter.title;
                }

                if (this.bulk_export_filter.start_date) {
                    params.start_date = this.bulk_export_filter.start_date;
                }

                if (this.bulk_export_filter.end_date) {
                    params.end_date = this.bulk_export_filter.end_date;
                }

                const url = `/api${url_api_list.apiDownloadDeliveryRecord}?${obj2Path(params)}`;

                await fetch(url, {
                    headers: {
                        'Accept-Language': this.$store.getters.language,
                        'Authorization': this.$store.getters.token,
                        'accept': 'application/json',
                    },
                }).then(async(response) => {
                    let filename = `視聴者データ.xlsx`;
                    filename = filename.replaceAll('"', '');

                    await response.blob().then((res) => {
                        this.file = res;
                    });

                    const fileURL = window.URL.createObjectURL(this.file);
                    const fileLink = document.createElement('a');

                    fileLink.href = fileURL;
                    fileLink.setAttribute('download', filename);
                    document.body.appendChild(fileLink);

                    fileLink.click();
                }).catch((error) => {
                    console.log(error);

                    this.$toast.danger({
                        content: this.$t('TOAST_HAVE_ERROR'),
                    });
                });

                this.file = null;
                this.handleCloseBulkExportModal();

                MakeToast({
                    variant: 'success',
                    title: this.$t('SUCCESS'),
                    content: 'データが正常にエクスポートされました。',
                });
            } catch (error) {
                console.error('[handleBulkExportMovies] ===>', error);
            } finally {
                this.overlay.show = false;
            }
        },
    },
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style lang="scss">
.multiselect--disabled {
    &>.multiselect__tags {
        background: #E9ECEF;
    }
}

.filter-select {
    &>.multiselect__tags {
        border-radius: 0px;
        min-height: 45px !important;
        border: 1px solid #ced4da;
    }

    &>.multiselect__tags>.multiselect__tags-wrap>.multiselect__tag {
        margin-top: 6px !important;
    }

    &>.multiselect__tags>.multiselect__placeholder {
        font-size: 16px !important;
        padding-top: 4px !important;
    }
}

.multiselect__tags {
    border-radius: 0px;
    padding-left: 12px;
    border-radius: 5px;
    border: 1px solid #ced4da;
    min-height: 40px !important;
    padding-top: 5px !important;
    padding-left: 10px !important;
}

.multiselect__tags>.multiselect__tags-wrap>.multiselect__tag {
    margin-top: 6px !important;
}

.multiselect__tags>.multiselect__placeholder {
    font-size: 16px !important;
    color: #495057;
}

.dropped {
    width: 100px;
    height: 25px;
    padding: 5px;
    display: flex;
    align-items: center;
    border-radius: 45px;
    white-space: nowrap;
    justify-content: center;
    background-color: #9B3922;

    &:hover {
        opacity: .6;
        cursor: pointer;
    }

    &>.dropped-text {
        font-size: 12px;
        color: #FFFFFF;
    }
}

.dz-clickable, .dz-message {
  width: 100% !important;
}

@media (min-width: 1920px) {
  .modal-xl {
    margin-left: 250px;
    max-width: 80%;
  }

    .b-calendar-inner {
        max-width: 300px !important;
        min-width: 300px !important;
    }
}

@media (max-width: 1240px) {
  .modal-xl {
    margin-left: 180px;
    max-width: 80%;
  }

    .b-calendar-inner {
        max-width: 240px !important;
        min-width: 240px !important;
    }
}
</style>

<style lang="scss" scoped>
body {
    position: relative;
}

.my-content {
    display: flex;
    position: relative;
    padding-bottom: 50px;
    justify-content: center;

    .item {
        padding: 10px 0px;
        border-top: 1px solid #dddddd;
        border-bottom: 1px solid #dddddd;
    }

    ;

    .icon-drag {
        font-size: 28px;

        &:hover {
            opacity: .6;
            cursor: pointer;
        }
    }

    .thumbnail {
        width: 250px;
        display: flex;
        height: 150px;
        position: relative;
        border-radius: 5px;

        &:hover {
            opacity: .6;
            cursor: pointer;
        }

        .thumbnail-img {
            width: 250px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }

        .video-length {
            right: 15px;
            opacity: .8;
            bottom: 10px;
            color: #FFFFFF;
            font-size: 12px;
            padding: 0px 5px;
            position: absolute;
            border-radius: 2px;
            background: #000000;
        }
    }

    .video-info {
        display: flex;
        padding-left: 20px;
        padding-right: 150px;
        flex-direction: column;
        align-items: flex-start;
        justify-content: space-around;

        .title {
            font-size: 20px;
            font-weight: 500;
        }

        .description {
            font-size: 18px;
            color: #3e3e3e;
        }
    }
}

.header-bar {
    display: flex;
    align-items: center;
}

::v-deep .mx-datepicker {
    max-width: 42px !important;
}

::v-deep .mx-input {
    margin-left: 5px;
    border-radius: 5px;
    height: 39px !important;
    background-color: #0F0448;
}

::v-deep .mx-icon-calendar {
    color: #FFFFFF !important;
}

.date-selector {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;

    button {
        background-color: #0F0448;

        &:active {
            background-color: #0F0448;
        }

        &:focus {
            background-color: #0F0448;
        }
    }

    button.date {
        padding: 0 4px;
        cursor: default;
        min-width: 120px;
        font-weight: 600;
    }

    button.minus-btn,
    button.plus-btn {
        &:hover {
            opacity: .8 !important;
            background-color: #0F0448;
        }
    }

    button.plus-btn {
        border-top-right-radius: 6px !important;
        border-bottom-right-radius: 6px !important;
    }

    button.minus-btn {
        border-top-left-radius: 6px !important;
        border-bottom-left-radius: 6px !important;
    }
}

::v-deep .prepend-filter {
    min-width: 150px !important;
}

::v-deep .prepend-filter-text {
    min-width: 125px !important;
}

.modal-register-label {
    display: flex;
    min-width: 150px;
    align-items: center;
}

.video-preview {
    height: 550px;
    width: 300px;
    border-radius: 5px;
}

.image-preview {
    height: 250px;
    width: auto;
    border-radius: 5px;
}

.button-save {
    width: 120px;
    height: 35px;
    background-color: #FF8A1F;
    color: #FFFFFF;

    &:hover {
        opacity: .6;
        cursor: pointer;
    }
}

.select-per-page {
    left: 15px;
    position: absolute;

    #per-page {
        width: 100px;
    }
}

.reset-padding-b-col {
    padding-left: 0px;
    padding-right: 0px;
}

.no-data {
    width: 100%;
    display: flex;
    padding: 30px;
    border-radius: 5px;
    align-items: center;
    justify-content: center;
    border: 1px solid #dddddd;
}

.tag-pill {
    width: 150px;
    margin-top: 10px;
    padding: 5px 10px;
    margin-right: 10px;
    text-align: center;
    border-radius: 45px;
    background-color: #D9D9D9;

    &:hover {
        opacity: .6;
        cursor: pointer;
    }
}

.function-button {
    right: 5px;
    height: 100%;
    display: flex;
    align-items: center;
    flex-direction: row;
    justify-content: center;

    &>button {
        width: 45px;
        height: 45px;
        border-radius: 45px;

        &:hover {
            opacity: .6;
            cursor: pointer;
        }
    }
}

.modal-confirm-delete {
    align-items: center;
    justify-content: center;

    &>button {
        width: 150px;

        &:hover {
            opacity: .6;
            cursor: pointer;
        }
    }

    &>button:nth-child(2) {
        color: #FFFFFF;
        background-color: #FF8A1F;
    }
}

::v-deep .custom-input-group {
    flex-wrap: nowrap !important;
}

.skeleton-cover {
    width: 250px;
    height: 150px;
    border-radius: 5px;
}

.my-calendar {
    gap: 20px;
    width: 80%;
    display: flex;
    flex-wrap: wrap;
    position: relative;
    align-items: center;
    flex-direction: row;
    justify-content: flex-start;

    &>.calendar-shell {
        width: 125px;
        height: 80px;
        display: flex;
        user-select: none;
        position: relative;
        border-radius: 5px;
        padding-left: 10px;
        margin-bottom: 10px;
        padding-right: 10px;
        align-items: center;
        flex-direction: column;
        border: 1px solid #dddddd;
        justify-content: space-evenly;
        box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 12px;

        &>.calendar-text {
            top: 5px;
            left: 5px;
            display: flex;
            color: #3e3e3e;
            font-size: 12px;
            padding: 0px 5px;
            user-select: none;
            font-weight: bold;
            position: absolute;
            border-radius: 45px;
        }

        &>.dropped {
            width: 100px;
            height: 25px;
            padding: 5px;
            display: flex;
            align-items: center;
            border-radius: 45px;
            white-space: nowrap;
            justify-content: center;
            background-color: #9B3922;

            &:hover {
                opacity: .6;
                cursor: pointer;
            }

            &>.dropped-text {
                font-size: 12px;
                color: #FFFFFF;
            }
        }

        .remove-movie {
            &:hover {
                opacity: .6;
                cursor: pointer;
            }
        }
    }
}

.my-movie-list {
    width: 20%;
    height: 630px;
    display: flex;
    overflow-y: scroll;
    padding-right: 10px;
    align-items: center;
    flex-direction: column;

    &::-webkit-scrollbar {
        width: 3px;
    }

    &>.movie-pill {
        width: 100%;
        height: 35px;
        display: flex;
        padding: 5px 5px;
        border-radius: 5px;
        align-items: center;
        white-space: nowrap;
        justify-content: center;
        background-color: #006769;

        &:hover {
            opacity: .6;
            cursor: pointer;
        }

        &>.movie-pill-text {
            font-size: 12px;
            color: #FFFFFF;
        }
    }
}

.main-frame {
    width: 100%;
    height: 100%;
    display: flex;
    color: #3e3e3e;
    flex-direction: column;

    .calendar-nav-bar {
        width: 100%;
        display: flex;
        padding: 10px;
        flex-direction: row;
        align-items: center;
        border: 1px solid #d9d9d9;

        .current-date-button {
            width: 100px;
            height: 40px;
            border-radius: 3px;
            background-color: #ffffff;
            border: 1px solid #d9d9d9 !important;

            &:focus,
            &:hover,
            &:active {
                opacity: .6;
                outline: none !important;
                background-color: #d6d6d6;
                box-shadow: none !important;
            }

            &>span {
                color: #3e3e3e;
            }
        }

        .icon-holder {
            margin-left: 30px;
            margin-right: 30px;

            .calendar-icon {
                font-size: 30px;

                &:hover {
                    opacity: .6;
                    cursor: pointer;
                }
            }

            .next-month-icon {
                margin-left: 20px;
            }
        }

        .current-date-text {
            font-size: 26px;
        }

        .show-calendar-button {
            right: 10px;
            width: 60px;
            height: 35px;
            position: absolute;
            background-color: #FFFFFF;
            border: 1px solid #d9d9d9 !important;

            &>i {
                font-size: 22px;
                color: #000000;
            }

            &:hover,
            &:focus,
            &:active {
                box-shadow: none !important;
            }
        }
    }

    .calendar-content {
        display: flex;
        flex-direction: row;
        border: 1px solid #d9d9d9;
        border-top: none !important;

        .calendar-general {
            width: 20%;
            display: flex;
            padding-top: 1%;
            align-items: center;
            flex-direction: column;
            border-right: 1px solid #d9d9d9;
        }

        .calendar-detail {
            .calendar-cell-top {
                height: 50px;
                color: #ffffff;
                padding: 10px 5px;
                background-color: #0f0448;
                box-shadow: 0 0 0 0.5px #d9d9d9;
            }

            .calendar-cell-empty {
                height: 120px;
                padding: 10px 5px;
            }

            .calendar-cell {
                height: 230px;
                padding: 10px 5px;
                box-shadow: 0 0 0 0.5px #d9d9d9;

                .grey-cell {
                    font-size: 12px !important;
                    color: #c6c0c0 !important;
                    font-weight: normal !important;
                }

                .cell-head {
                    height: 20%;
                    color: #3e3e3e;
                    display: flex;
                    font-size: 12px;
                    font-weight: bolder;
                    align-items: center;
                    flex-direction: column;
                    justify-content: center;
                }

                .cell-body {
                    height: 85%;
                    display: flex;
                    font-size: 10px;
                    flex-wrap: nowrap;
                    align-items: center;
                    flex-direction: column;
                    justify-content: center;

                    .movie-pill {
                        width: 85%;
                        font-weight: bold;
                        color: #FFFFFF;
                        padding: 3px 3px 3px 5px;
                        border-top-left-radius: 15px;
                        border-bottom-left-radius: 15px;
                        background-color: #009688;

                        &:hover {
                            opacity: .6;
                            cursor: pointer;
                        }
                    }

                    .movie-pill-delete {
                        width: 10%;
                        display: flex;
                        color: #000000;
                        font-size: 14px;
                        align-items: center;
                        justify-content: center;
                        background-color: #FF9800;
                        border-top-right-radius: 15px;
                        border-bottom-right-radius: 15px;

                        &:hover {
                            opacity: .6;
                            cursor: pointer;
                        }
                    }
                }
            }
        }
    }
}

.col-2 {
    flex: 0 0 14.28% !important;
    max-width: 14.28% !important;
}

.draggable-form {
    top: 50%;
    left: 50%;
    width: 425px;
    z-index: 1051;
    border-radius: 10px;
    position: absolute;
    background-color: #ffffff;
    box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;

    .header {
        width: 100%;
        height: 45px;
        cursor: move;
        display: flex;
        padding: 10px;
        color: #ffffff;
        font-weight: bold;
        align-items: center;
        flex-direction: row;
        background-color: #0F0448;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        justify-content: space-between;

        &>.icon-holder {
            width: 30px;
            padding: 5px;
            height: 30px;
            text-align: center;
            border-radius: 45px;

            &:hover {
                opacity: .6;
                cursor: pointer;
                background-color: #FFFFFF;

                &>i {
                    color: #3e3e3e;
                }
            }
        }
    }

    .content {
        width: 100%;
        display: flex;
        padding: 10px;
        max-height: 450px;
        overflow-y: scroll;
        flex-direction: column;

        &::-webkit-scrollbar-thumb {
            border-radius: 5px !important;
        }
    }
}

.slide-enter-active {
    transition: all 0.5s;
}

.slide-leave-active {
    transition: all 0.15s ease-in;
}

.slide-enter,
.slide-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}

.upload-video-input {
    width: 100%;
    height: 38px;
    display: flex;
    padding-left: 10px;
    align-items: center;
    border: 1px solid #dee2e6;

    &>span {
        user-select: none;
    }
}

.table-delivery-record-holder {
    overflow-y: auto;
    border-spacing: 0px !important;
    border-collapse: separate !important;

    .table-delivery-record-th {
        font-weight: bold;
        color: #FFFFFF;
        text-align: center;
        background-color: #0F0448;
    }

    .table-delivey-record-td {
        text-align: center;
    }
}

.button-export-csv {
    width: 100px;
    color: #FFFFFF;
    background-color: #399918;

    &:hover {
        opacity: .6;
    }
}

.button-add-movie {
    width: 100%;
    height: 35px;
    color: #FFFFFF;
    background-color: #399918;

    &:hover {
        opacity: .6;
    }
}

.bulk-export-filter-section {
  border: 1px solid #dee2e6;
  border-radius: 5px;
  background-color: #f8f9fa;
}

.bulk-export-list-holder {
  min-height: 400px;
  max-height: 400px;
  overflow-y: auto;
  border: 1px solid #dee2e6;
  border-radius: 5px;

  table {
    border-spacing: 0px !important;
    border-collapse: separate !important;
  }

  &::-webkit-scrollbar {
    width: 8px;
  }

  &::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  &::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;

    &:hover {
      background: #555;
    }
  }

  .bulk-export-th {
    position: sticky;
    top: 0;
    z-index: 10;
    font-weight: bold;
    color: #FFFFFF;
    text-align: center;
    background-color: #0F0448;
  }

  .bulk-export-td {
    text-align: center;
    vertical-align: middle;
  }
}

.selected-count-text {
  font-weight: bold;
  color: #0F0448;
  font-size: 14px;
}

.toggle-loop-container {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 8px;
  padding: 4px;

  ::v-deep .custom-control-input:checked ~ .custom-control-label::before {
    background-color: #28a745;
    border-color: #28a745;
  }

  ::v-deep .custom-control-input:not(:checked) ~ .custom-control-label::before {
    background-color: #6c757d;
    border-color: #6c757d;
  }

  ::v-deep .custom-control-label {
    font-size: 12px;
    color: #3e3e3e;
    user-select: none;
    white-space: nowrap;
  }

  ::v-deep .custom-switch {
    padding-left: 2.25rem;
  }
}

@media (max-width: 768px) {
  .toggle-loop-container {
    margin-top: 6px;

    ::v-deep .custom-control-label {
      font-size: 11px;
    }
  }
}

.bulk-export-filter-section {
  border: 1px solid #dee2e6;
  border-radius: 5px;
  background-color: #f8f9fa;
}

.bulk-export-list-holder {
  min-height: 400px;
  max-height: 400px;
  overflow-y: auto;
  border: 1px solid #dee2e6;
  border-radius: 5px;

  table {
    border-spacing: 0px !important;
    border-collapse: separate !important;
  }

  &::-webkit-scrollbar {
    width: 8px;
  }

  &::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  &::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;

    &:hover {
      background: #555;
    }
  }

  .bulk-export-th {
    position: sticky;
    top: 0;
    z-index: 10;
    font-weight: bold;
    color: #FFFFFF;
    text-align: center;
    background-color: #0F0448;
  }

  .bulk-export-td {
    text-align: center;
    vertical-align: middle;
  }
}

.selected-count-text {
  font-weight: bold;
  color: #0F0448;
  font-size: 14px;
}

.toggle-loop-container {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 8px;
  padding: 4px;

  ::v-deep .custom-control-input:checked ~ .custom-control-label::before {
    background-color: #28a745;
    border-color: #28a745;
  }

  ::v-deep .custom-control-input:not(:checked) ~ .custom-control-label::before {
    background-color: #6c757d;
    border-color: #6c757d;
  }

  ::v-deep .custom-control-label {
    font-size: 12px;
    color: #3e3e3e;
    user-select: none;
    white-space: nowrap;
  }

  ::v-deep .custom-switch {
    padding-left: 2.25rem;
  }
}

@media (max-width: 768px) {
  .toggle-loop-container {
    margin-top: 6px;

    ::v-deep .custom-control-label {
      font-size: 11px;
    }
  }
}
</style>
