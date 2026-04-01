<template>
	<b-form-select
		:id="id"
		v-model="data"
		:options="dataOptions"
		:type="type"
		:class="className"
		:style="styleEl"
		:placeholder="placeholder"
		:readonly="readonly"
		:disabled="disabled"
		:validate="validate"
		:size="size"
		@input="setEmit()"
	/>
</template>

<script>
export default {
    name: 'VSelect',
    props: {
        id: {
            type: String,
            require: false,
            default: '',
        },
        type: {
            type: String,
            require: false,
            default: 'text',
            validate: value => {
                return ['text', 'number', 'email', 'password', 'search', 'url', 'tel', 'date', 'time', 'range', 'range'].includes(value);
            },
        },
        className: {
            type: String,
            require: false,
            default: 'v-custom-select',
        },
        styleEl: {
            type: String,
            require: false,
            default: '',
        },
        value: {
            type: [Number, String],
            require: true,
            default: null,
        },
        placeholder: {
            type: String,
            require: true,
            default: function() {
                return this.$t('PLACEHOLDER_INPUT');
            },
        },
        readonly: {
            type: Boolean,
            require: false,
            default: false,
        },
        disabled: {
            type: Boolean,
            require: false,
            default: false,
        },
        validate: {
            type: Boolean,
            require: false,
            default: false,
        },
        dataOptions: {
            type: Array,
            require: false,
            default() {
                return [
                    { value: null, text: this.$t('PLEASE_SELECT'), disabled: false },
                ];
            },
        },
        size: {
            type: String,
            require: false,
            default: 'md',
        },
    },
    data() {
        return {
            data: '',
        };
    },
    computed: {
        dataChange() {
            return this.data;
        },
    },
    watch: {
        dataChange() {
            this.setEmit();
        },
        value() {
            this.setData();
        },
    },
    created() {
        this.setData();
        this.setEmit();
    },
    methods: {
        setData() {
            this.data = this.value;
        },
        setEmit() {
            this.$emit('input', this.data);
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    .v-custom-select {
        // border: 1px solid $black;
        // min-height: 40px;
    }
</style>
