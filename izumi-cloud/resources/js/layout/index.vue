<template>
	<div id="wrapper" :class="toggle">
		<Sidebar :toggle-bool="toggleBool" />
		<div id="page-content-wrapper">
			<Navbar @toggle="toggleMenu()" />
			<div :class="appMainStyle">
				<AppMain />
			</div>
		</div>
	</div>
</template>

<script>
import Navbar from './components/Navbar/index';
import Sidebar from './components/Sidebar/index';
import AppMain from './components/AppMain';

export default {
    name: 'Layout',
    components: {
        Navbar,
        Sidebar,
        AppMain,
    },
    data() {
        return {
            toggle: '',
            toggleBool: false,
            isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
            appMainStyle: 'calc(100vh - 69px); overflow: auto;',
        };
    },
    created() {
        if (this.isMobile) {
            this.appMainStyle = 'calc(100vh - 69px); overflow-y: scroll !important; overflow-x: hidden !important;';
        }
    },
    methods: {
        toggleMenu() {
            this.toggleBool = !this.toggleBool;
            if (this.toggleBool === true) {
                this.toggle = 'toggled';
            } else {
                this.toggle = '';
            }
        },
    },
};
</script>

<style lang="scss">
@import "@/scss/variables.scss";
@import "@/scss/modules/layout.scss";

</style>
