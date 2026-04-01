<template>
	<b-table
		:class="className"
		:items="items"
		:striped="striped"
		:bordered="bordered"
		:borderless="borderless"
		:outlined="outlined"
		:small="small"
		:hover="hover"
		:dark="dark"
		:foot-clone="footClone"
		:no-border-collapse="noCollapse"
		:fields="fields"
		:head-variant="headVariant"
		:table-variant="tableVariant"
		responsive="sm"
	>
		<template v-for="(_, slotName) of $scopedSlots" #[slotName]="scope">
			<slot :name="slotName" v-bind="scope" />
		</template>
	</b-table>
</template>

<script>
export default {
    name: 'VTableHeader',
    props: {
        className: {
            type: String,
            require: false,
            default: '',
        },
        fields: {
            type: Array,
            require: false,
            default() {
                return [];
            },
        },
        items: {
            type: Array,
            require: false,
            default() {
                return [];
            },
        },
        tableVariants: {
            type: Array,
            require: false,
            default() {
                return [];
            },
        },
        striped: {
            type: Boolean,
            require: false,
            default: false,
        },
        bordered: {
            type: Boolean,
            require: false,
            default: false,
        },
        borderless: {
            type: Boolean,
            require: false,
            default: false,
        },
        outlined: {
            type: Boolean,
            require: false,
            default: false,
        },
        small: {
            type: Boolean,
            require: false,
            default: false,
        },
        hover: {
            type: Boolean,
            require: false,
            default: false,
        },
        dark: {
            type: Boolean,
            require: false,
            default: false,
        },
        footClone: {
            type: Boolean,
            require: false,
            default: false,
        },
        headVariant: {
            type: String,
            require: false,
            default: null,
        },
        tableVariant: {
            type: String,
            require: false,
            default: '',
        },
        noCollapse: {
            type: Boolean,
            require: false,
            default: false,
        },
    },
};
</script>

<style lang="scss" scoped>

</style>
