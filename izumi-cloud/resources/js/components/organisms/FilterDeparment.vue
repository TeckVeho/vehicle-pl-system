<template>
	<div class="filter-department">
		<b-row>
			<b-col cols="10" class="mt-3">
				<span>{{ $t('SELECT_LOCATION') }}</span>
				<b-form-select
					id="filter-affiliation-base-value"
					v-model="selectFileClassification"
					:options="listFileClassification"
					:value-field="'id'"
					:text-field="'department_name'"
				>
					<template #first>
						<b-form-select-option :value="null">
							{{ $t('PLEASE_SELECT') }}
						</b-form-select-option>
					</template>
				</b-form-select>
			</b-col>
		</b-row>
	</div>
</template>

<script>
import vButton from '@/components/atoms/vButton';

export default {
    name: 'VFilterDeparment',
    comments: {
        vButton,
    },
    props: {
        listFileClassification: {
            type: Array,
            default: () => {
                return [
                    {
                        id: 1,
                        type: this.$t('PLEASE_SELECT'),
                    },
                ];
            },
            required: true,
        },
        value: {
            type: Number,
            required: false,
            default: null,
        },
    },
    data() {
        return {
            selectFileClassification: this.value,
        };
    },
    watch: {
        selectFileClassification(newVal) {
            this.$emit('input', newVal);
        },
    },
    methods: {
    },
};
</script>

<style>

</style>
