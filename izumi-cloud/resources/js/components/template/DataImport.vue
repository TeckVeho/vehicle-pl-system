<template>
	<div class="data-import-template">

		<!-- PAGE HEADER -->
		<vHeaderPage class="data-import__title-header">{{ $t('PAGE_TITLE.DATA_IMPORT') }}</vHeaderPage>

		<!-- IMPORT CSV FIELD -->
		<DataImportCSVField />

	</div>
</template>

<script>
// Atomic components import
import vHeaderPage from '@/components/atoms/vHeaderPage';
import DataImportCSVField from '@/components/organisms/DataImportCSVField';

export default {
    name: 'DataImportTemplate',
    components: {
        vHeaderPage,
        DataImportCSVField,
    },

};
</script>
