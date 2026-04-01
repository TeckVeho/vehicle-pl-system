<template>
	<b-form-textarea
		:id="id"
		v-model="data"
		:class="className"
		:placeholder="placeholder"
		:rows="rows"
		:max-rows="maxRows"
		:readonly="readonly"
		:disabled="disabled"
		:value="value"
	/>
</template>

<script>
export default {
    name: 'VTextarea',
    props: {
        id: {
            type: String,
            require: false,
            default: '',
        },
        className: {
            type: String,
            require: false,
            default: '',
        },
        value: {
            type: String,
            require: true,
            default: '',
        },
        placeholder: {
            type: String,
            require: true,
            default: function() {
                return '';
            },
        },
        rows: {
            type: Number,
            require: true,
            default: 3,
        },
        maxRows: {
            type: Number,
            require: true,
            default: 5,
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

