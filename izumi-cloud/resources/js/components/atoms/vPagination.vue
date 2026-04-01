<template>
	<b-pagination
		v-model="page"
		:per-page="perPage"
		:total-rows="totalRows"
		pills
		first-number
		last-number
		:aria-controls="ariaControls"
		:page-class="pageClass"
		:next-class="nextClass"
		:prev-class="prevClass"
	>
		<template #next-text>
			<i class="fas fa-angle-right" />
		</template>

		<template #prev-text>
			<i class="fas fa-angle-left" />
		</template>
	</b-pagination>
</template>

<script>
export default {
    name: 'VPagination',
    props: {
        currentPage: {
            type: Number,
            require: true,
            default: 1,
            validate: value => {
                return value > 0;
            },
        },
        perPage: {
            type: Number,
            require: true,
            default: 20,
            validate: value => {
                return value > 0;
            },
        },
        totalRows: {
            type: Number,
            require: true,
            default: 0,
            validate: value => {
                return value >= 0;
            },
        },
        ariaControls: {
            type: String,
            require: false,
            default: '',
            validate: value => {
                return value;
            },
        },
        pageClass: {
            type: String,
            require: false,
            default: '',
            validate: value => {
                return value;
            },
        },
        nextClass: {
            type: String,
            require: false,
            default: '',
            validate: value => {
                return value;
            },
        },
        prevClass: {
            type: String,
            require: false,
            default: '',
            validate: value => {
                return value;
            },
        },
    },
    data() {
        return {
            page: 1,
        };
    },
    computed: {
        currentPageChange() {
            return this.page;
        },
        watchCurrent() {
            return this.currentPage;
        },
    },
    watch: {
        currentPageChange() {
            this.setEmit();
        },
        watchCurrent() {
            this.page = this.currentPage;
        },
    },
    created() {
        this.page = this.currentPage;
    },
    methods: {
        setEmit() {
            this.$emit('currentPageChange', this.page);
        },
    },
};
</script>
