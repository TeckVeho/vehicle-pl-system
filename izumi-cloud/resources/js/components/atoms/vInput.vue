<template>
	<b-form-input
		:id="id"
		v-model="data"
		:name="name"
		:type="type"
		:class="className"
		:style="styleEl"
		:placeholder="placeholder"
		:readonly="readonly"
		:disabled="disabled"
		:validate="validate"
		:autocomplete="autocomplete"
		@input="setEmit()"
		@keyup.enter="hitEnter"
	/>
</template>

<script>
export default {
    name: 'VInput',
    props: {
        id: {
            type: String,
            require: false,
            default: '',
        },
        type: {
            type: [String, Number],
            require: false,
            default: 'text',
            validate: value => {
                return ['text', 'number', 'email', 'password', 'search', 'url', 'tel', 'date', 'time', 'range', 'range'].includes(value);
            },
        },
        className: {
            type: String,
            require: false,
            default: 'v-custom-input',
        },
        styleEl: {
            type: String,
            require: false,
            default: '',
        },
        value: {
            type: [String, Number],
            require: true,
            default: '',
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
        name: {
            type: String,
            require: false,
            default: '',
        },
        autocomplete: {
            type: String,
            require: false,
            default: '',
        },
        hitEnter: {
            type: Function,
            require: false,
            default: () => {
                console.log('hitEnter');
            },
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
        setDataChange() {
            return this.value;
        },
    },
    watch: {
        dataChange() {
            this.setEmit();
        },
        setDataChange() {
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
</style>
