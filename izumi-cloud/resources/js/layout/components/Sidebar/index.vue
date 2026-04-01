<template>
	<div id="sidebar-wrapper">
		<MenuSidebar
			class="sidebar-menu"
			style="height: 100%"
			:routes="routes"
			:toggole="sideBar"
		/>
	</div>
</template>

<script>
import MenuSidebar from './Menu.vue';

export default {
    name: 'Sidebar',
    components: {
        MenuSidebar,
    },
    props: {
        toggleBool: {
            type: Boolean,
            require: true,
        },
    },
    data() {
        return {
            sideBar: '',
            width: window.innerWidth,
        };
    },
    computed: {
        routes() {
            return this.$store.getters.permissionRoutes.filter((item) => item.hidden !== true);
        },
    },
    watch: {
        width() {
            if (this.toggleBool === false) {
                this.sideBar = this.isCheckSidebar(this.width);
            } else if (this.toggleBool === true && this.width <= 768) {
                this.sideBar = true;
            }
        },
        toggleBool() {
            if (this.toggleBool === true && this.width > 768) {
                this.sideBar = false;
            }

            if (this.toggleBool === false && this.width > 768) {
                this.sideBar = true;
            }

            if (this.toggleBool === false && this.width <= 768) {
                this.sideBar = false;
            }

            if (this.toggleBool === true && this.width <= 768) {
                this.sideBar = true;
            }
        },
        sideBar() {
            this.$bus.emit('toggleBool', this.toggleBool);
        },
    },
    created() {
        window.addEventListener('resize', this.onResize);
    },
    destroyed() {
        window.removeEventListener('resize', this.onResize);
    },
    mounted() {
        this.sideBar = this.isCheckSidebar(this.width);
    },
    methods: {
        onResize(e) {
            this.width = window.innerWidth;
        },
        isCheckSidebar(value) {
            if (value > 768) {
                return true;
            }

            return false;
        },
    },
};
</script>

<style lang="scss" scoped>
	@import "@/scss/variables.scss";
	@import "@/scss/modules/layout.scss";
</style>
