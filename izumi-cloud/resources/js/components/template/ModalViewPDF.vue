<template>
	<div class="modal-view-pdf">
		<b-modal
			v-model="show"
			no-close-on-backdrop
			no-close-on-esc
			hide-footer
			:static="true"
			id="pdf-preview-modal"
			size="xl"
			centered
			:title="pdfTitle"
		>
			<div class="pdf-viewer">
				<iframe
					v-if="pdfUrl"
					:src="pdfUrl + '#toolbar=0&navpanes=0&zoom=page-width'"
					frameborder="0"
					class="pdf-iframe"
				/>
				<iframe
					v-if="pdfUrlBack"
					:src="pdfUrlBack + '#toolbar=0&navpanes=0&zoom=page-width'"
					frameborder="0"
					class="pdf-iframe"
				/>
				<div v-if="!pdfUrl && !pdfUrlBack" class="text-center py-4 w-100">
					<p>{{ $t('NO_PDF_AVAILABLE') }}</p>
				</div>
			</div>
		</b-modal>
	</div>
</template>

<script>
export default {
    name: 'ModalViewPDF',
    props: {
        pdfTitle: {
            type: String,
            default: 'PDF Preview',
        },
        pdfUrl: {
            type: String,
            required: true,
            default: '',
        },
        pdfUrlBack: {
            type: String,
            required: false,
            default: '',
        },
        isShowModal: {
            type: Boolean,
            default: false,
            required: true,
        },
    },
    computed: {
        show: {
            get() {
                return this.isShowModal;
            },
            set(val) {
                this.$emit('update:isShowModal', val);
            },
        },
    },
};
</script>

<style>
.pdf-viewer {
  width: 100%;
  height: 70vh;          /* thấp hơn 100vh để vừa trong modal */
  display: flex;
  gap: 8px;
  overflow: hidden;      /* tránh tràn */
}

.pdf-iframe {
  flex: 1 1 0;           /* chia đều 2 bên, nếu chỉ có 1 thì full chiều ngang */
  width: 100%;
  height: 100%;
  border: none;
  display: block;
}
</style>
