<!-- eslint-disable vue/no-parsing-error -->
<template>
	<b-overlay
		:blur="overlay.blur"
		:show="overlay.show"
		:rounded="overlay.sm"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
	>
		<template v-if="!sub_overlay.show" #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p style="margin-top: 10px">{{ $t("PLEASE_WAIT") }}</p>
			</div>
		</template>

		<div class="header">
			<vHeaderPage>{{ $t("PAGE_TITLE.IZUMI_NOTE_BOOK") }}</vHeaderPage>
		</div>

		<div class="mt-4">
			<div style="display: flex; justify-content: space-between;">
				<div style="width: 200px;" class="input-row">
					<b-form-select class="my-custom-select" v-model="date" :options="options_date" @change="onDateChange" />
				</div>

				<div>
					<b-button class="btn-files" @click="handleOpenModalUploadFile()">
						<span>ファイルをアップロード</span>
					</b-button>
				</div>
			</div>

			<div v-if="uploadingFiles.length > 0" class="mt-3">
				<div v-for="(file, idx) in uploadingFiles" :key="`uploading-file-${idx}`" class="mb-2">
					<div class="d-flex justify-content-between align-items-center mb-1">
						<span style="font-size: 13px; color: #333;">{{ file.file_name }}</span>
						<span style="font-size: 12px; color: #666;">
							{{ `${transformPercentage(file?.progressPercentage)} %` }}
						</span>
					</div>
					<b-progress
						:max="100"
						height="1.5rem"
						:striped="file?.progressPercentage < 100"
						style="margin-top: 5px;"
					>
						<b-progress-bar
							:value="file?.progressPercentage"
							:variant="file?.progressPercentage === 100 ? 'success' : 'primary'"
						>
							<span style="font-size: 11px; color: #ffffff;">
								{{ file?.progressPercentage < 100 ? `${transformBandwidth(file?.progressBandwidth)} MB` : '完了' }}
							</span>
						</b-progress-bar>
					</b-progress>
				</div>
			</div>

			<div class="mt-4">
				<div v-if="files.length > 0" class="form-files">
					<div v-for="(group, groupIndex) in files" :key="groupIndex" class="mb-3">
						<template v-if="group.data.length > 0">
							<div class="d-flex flex-column align-items-start">
								<div class="mb-3">
									<span v-if="groupIndex === 0 || files[groupIndex - 1].tag_name !== group.tag_name" style="font-weight: bold; font-size: 18px; color: #0F0448;">
										{{ group.tag_name }}
									</span>
								</div>

								<draggable
									:list="group.data"
									handle=".handle"
									:animation="100"
									@change="dragChanged(groupIndex)"
									@end="dragging = false"
									@start="dragging = true"
									:class="{ [`cursor-grabbing`]: dragging === true }"
								>
									<div v-for="(file, index) in group.data" :key="index" class="file-card">
										<i class="fas fa-bars handle" style="cursor: move; margin-right: 10px;" />

										<i class="fas fa-paperclip" />

										<span class="d-flex span-csv" @click="e => goToDownload(file.file_url)">
											{{ file.file_name }}
										</span>

										<b-button class="btn-delete-file" @click="handleClosefiles(file.id)">
											<i class="fal fa-trash-alt" style="color: red; font-size: 16px;" />
										</b-button>
									</div>
								</draggable>
							</div>
						</template>
					</div>
				</div>

				<div class="form-files d-flex flex-row justify-content-center align-items-center" v-else>
					<span style="color: #000000;">データがありません</span>
				</div>
			</div>

			<b-modal
				hide-header
				id="modal-cf"
				:static="true"
				no-close-on-esc
				no-close-on-backdrop
				v-model="showModalDeleteFile"
				content-class="modal-custom-body"
				header-class="modal-custom-header"
				footer-class="modal-custom-footer"
			>
				<template #default>
					<span>このPDFを削除してよろしいですか。</span>
				</template>

				<template #modal-footer>
					<b-button class="modal-btn btn-cancel" style="background-color: #DDDDDD;" @click="showModalDeleteFile = false">
						<span style="color: #000000;">{{ $t("NO") }}</span>
					</b-button>

					<b-button class="modal-btn btn-apply" @click="handleDelete()">
						{{ $t("YES") }}
					</b-button>
				</template>
			</b-modal>

			<b-modal
				size="xl"
				hide-footer
				id="modal-pdf"
				:static="true"
				no-close-on-esc
				no-close-on-backdrop
				v-model="showModalPdf"
			>
				<div class="pdf-container">
					<iframe v-if="pdfUrl" :src="pdfUrl" width="100%" height="600px" frameborder="0" />
				</div>
			</b-modal>

			<b-modal
				size="xl"
				hide-footer
				id="modal-upload"
				:static="true"
				no-close-on-esc
				no-close-on-backdrop
				v-model="showModalUpload"
				title="ファイルのアップロード"
				@hidden="handleModalUploadClose()"
			>
				<b-overlay
					:blur="sub_overlay.blur"
					:show="sub_overlay.show"
					:rounded="sub_overlay.sm"
					:variant="sub_overlay.variant"
					:opacity="sub_overlay.opacity"
				>
					<template #overlay>
						<div class="text-center">
							<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
							<p style="margin-top: 10px">{{ $t("PLEASE_WAIT") }}</p>
						</div>
					</template>

					<div class="mb-3 flex-row">
						<span class="note-text">
							<span class="text-danger font-weight-bold">(*)</span>
							<span>複数ファイルをアップロードする場合は、CTRL + クリックで複数ファイルを選択してください。</span>
						</span>
					</div>

					<div v-if="totalFilesToUpload > 0" class="mb-3 p-3" style="background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #007bff;">
						<div class="d-flex justify-content-between align-items-center mb-3">
							<span style="font-weight: 600; color: #333;">
								アップロード進行中: {{ completedFilesCount }} / {{ totalFilesToUpload }} ファイル
							</span>
							<b-spinner v-if="completedFilesCount < totalFilesToUpload" small variant="primary" />
						</div>

						<div v-for="(file, idx) in uploadingFiles" :key="`modal-uploading-${idx}`" class="mb-2">
							<div class="d-flex justify-content-between align-items-center mb-1">
								<span style="font-size: 13px; color: #555;">
									<i class="fas fa-file-alt mr-1" />
									{{ file.file_name }}
								</span>
								<span v-if="file.progressPercentage >= 0" style="font-size: 12px; color: #666;">
									{{ file.progressPercentage === 100 ? '✓ 完了' : `${transformPercentage(file.progressPercentage)}%` }}
								</span>
								<span v-else style="font-size: 12px; color: #dc3545;">
									✗ 失敗
								</span>
							</div>
							<b-progress
								v-if="file.progressPercentage >= 0 && file.progressPercentage < 100"
								:max="100"
								height="0.75rem"
								:striped="true"
							>
								<b-progress-bar
									:value="file.progressPercentage"
									variant="primary"
								/>
							</b-progress>
						</div>
					</div>

					<div class="flex-row">
						<b-form-select
							text-field="text"
							v-model="fileType"
							value-field="value"
							disabled-field="disabled"
							:options="fileTypeOptions"
							class="my-second-custom-select"
						/>

						<span class="d-flex mt-2" v-if="isShowValidtionText" style="color: red;">まずファイルの種類を選択してください</span>
					</div>

					<div class="mt-3 mb-1">
						<span>ファイルを選択</span>
					</div>

					<template v-if="fileType">
						<vuedropzone
							id="customdropzonefile"
							ref="myVueDropzoneFile"
							:use-custom-slot="true"
							:include-styling="false"
							:options="dropzoneFileOptions"
							@vdropzone-sending="beforeSend"
							@vdropzone-file-added="afterAdded($event)"
							@vdropzone-complete="afterComplete($event)"
							@vdropzone-upload-progress="onUploadProgress($event)"
							@vdropzone-error="onError($event)"
						>
							<div class="d-flex flex-row justify-content-center align-items-center dropzone-custom-content">
								<span>画像ファイル</span>
							</div>
						</vuedropzone>
					</template>

					<template v-else>
						<div class="d-flex flex-row justify-content-center align-items-center dropzone-custom-content-disabled" @click="handleClickNonUpdate()">
							<span style="color: black;">画像ファイル</span>
						</div>
					</template>
				</b-overlay>
			</b-modal>
		</div>
	</b-overlay>
</template>

<script src="./script.js" />
<style src="./style.scss" lang="scss" scoped />
