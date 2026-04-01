<template>
	<label :for="forId" :class="className">
		{{ textLabel }}
	</label>
</template>

<script>
export default {
    name: 'VLabel',
    props: {
        forId: {
            type: String,
            require: false,
            default: '',
        },
        textLabel: {
            type: String,
            require: false,
            default: '',
        },
        className: {
            type: String,
            require: false,
            default: '',
        },
    },

};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    .user-regis-label {
        font-weight: bolder;
        font-size: 22px;
        color: $pale-sky;
    }

    label {
        font-weight: 500;
    }

    .label-search {
        font-size: 16px;
        margin-top: 8px;
        min-width: 110px;
        padding: 0 30px 0 30px;
    }
</style>
