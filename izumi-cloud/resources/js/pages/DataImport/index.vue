<template>
	<b-overlay
		:show="overlay.show"
		:variant="overlay.variant"
		:opacity="overlay.opacity"
		:blur="overlay.blur"
		:rounded="overlay.sm"
	>
		<!-- Template overlay -->
		<template #overlay>
			<div class="text-center">
				<b-icon icon="arrow-clockwise" font-scale="3" animation="spin" />
				<p style="margin-top: 10px;">{{ $t('PLEASE_WAIT') }}</p>
			</div>
		</template>
		<b-col>
			<div class="data-import">
				<DataImportTemplate />
				<div class="text-center footer-button">
					<vButton :class-name="'v-button-import-data v-button-default'" :text-button="$t('DATA_IMPORT.IMPORT')" @click.native="doImportData()" />
				</div>
			</div>
		</b-col>
	</b-overlay>
</template>

<script>
// Apis import
import { uploadData } from '@/api/modules/dataImport';
const API_URL = {
    urlUploadData: '/upload',
};

// Atomic components import
import DataImportTemplate from '@/components/template/DataImport';
import vButton from '@/components/atoms/vButton';

export default {
    name: 'DataImportIndex',
    components: {
        DataImportTemplate,
        vButton,
    },
    data() {
        return {
            nameCSVInput: '',
            fileInput: '',
            nameSelectedData: '',
            idSelectedData: '',
            yearMonth: '',
            overlay: {
                show: false,
                variant: 'light',
                opacity: 1,
                blur: '1rem',
                rounded: 'sm',
            },
        };
    },
    created() {
        this.$bus.on('idSelectedData', (value) => {
            this.idSelectedData = value;
        });

        this.$bus.on('nameCSVInput', (value) => {
            this.nameCSVInput = value;
        });

        this.$bus.on('fileInput', (data) => {
            this.fileInput = data;
        });

        this.$bus.on('overlayDataImport', (value) => {
            this.overlay.show = value;
        });

        this.$bus.on('yearMonth', (value) => {
            this.yearMonth = value;
        });
    },
    destroyed() {
        this.$bus.off('nameCSVInput');
        this.$bus.off('fileInput');
        this.$bus.off('idSelectedData');
        this.$bus.off('overlayDataImport');
        this.$bus.off('yearMonth');
    },
    methods: {
        async doImportData() {
            const formData = new FormData();

            formData.append('data_connection_id', this.idSelectedData);
            formData.append('file', this.fileInput[0]);

            const date = `${this.yearMonth}-01`;

            formData.append('date', date);

            try {
                const res = await uploadData(API_URL.urlUploadData, formData);

                if (res['code'] === 200) {
                    this.$toast.success({
                        content: this.$t('IMPORT_DATA_CSV_SUCCESS'),
                    });
                } else {
                    this.$toast.danger({
                        content: res['message'],
                    });
                }
            } catch (err) {
                this.$toast.danger({
                    content: err['response']['data']['message'],
                });
            }
        },
    },
};
</script>

<style lang="scss" scoped>
.data-import {
    min-height: calc(100vh - 89px);
}

.footer-button {
  margin-bottom: 40px;
}
</style>
