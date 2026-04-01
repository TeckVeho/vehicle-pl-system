<template>
	<b-form-checkbox
		v-model="data"
		:class="className"
		:size="size"
		@input="setEmit()"
	/>
</template>

<script>
export default {
    name: 'VCheckbox',
    props: {
        className: {
            type: String,
            require: false,
            default: '',
        },
        size: {
            type: String,
            require: false,
            default: '',
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
