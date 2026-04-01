<template>
	<div class="nav-bar">
		<b-navbar toggleable="lg" type="dark" align="center">
			<b-navbar-brand @click.prevent.stop="$emit('toggle')">
				<i id="toggle-menu" class="fas fa-bars" />
			</b-navbar-brand>

			<b-navbar-nav class="izumi-logo-container">
				<span class="izumi-logo">IZUMI</span>
			</b-navbar-nav>

			<b-navbar-toggle target="nav-collapse">
				<template #default="{ expanded }">
					<i v-if="expanded" class="fas fa-angle-up" />
					<i v-else class="fas fa-angle-down" />
				</template>
			</b-navbar-toggle>

			<b-collapse id="nav-collapse" is-nav>
				<b-navbar-nav class="ml-auto">
					<b-nav-item>
						<vButton class="btn-show-emp-name">
							<template #custom>
								<i class="fas fa-user icon-navbar" />
								<span>{{ employeeName }}</span>
							</template>
						</vButton>
					</b-nav-item>

					<b-nav-item>
						<vButton class="btn-logout" @click.native="doLogout()">
							<template #custom>
								<i class="fas fa-sign-out icon-navbar" />
								{{ $t('NAVBAR_LOGOUT') }}
							</template>
						</vButton>
					</b-nav-item>
				</b-navbar-nav>
			</b-collapse>
		</b-navbar>
	</div>
</template>

<script>
import logo from '@/assets/images/logo-navbar.png';
import vButton from '@/components/atoms/vButton';

export default {
    name: 'Navbar',
    components: {
        vButton,
    },
    data() {
        return {
            logo: logo,
        };
    },
    computed: {
        employeeName() {
            return this.$store.getters.name;
        },
    },
    methods: {
        doLogout() {
            this.$store.dispatch('user/doLogout')
                .then(() => {
                    this.$toast.success({
                        content: this.$t('LOGOUT_SUCCESS'),
                    });

                    this.$router.push('/login');
                })
                .catch(() => {
                    this.$toast.danger({
                        content: this.$t('TOAST_HAVE_ERROR'),
                    });
                });
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';
    @import '@/scss/modules/layout.scss';

    .izumi-logo-container {
      width: 80%;
      height: 100%;

      .izumi-logo {
        margin-left: 20px;
        line-height: 5px !important;
        font-size: 40px;
        font-weight: 800;
        color: #2787de !important;
        -moz-transform: scale(1.5, 1); /* Firefox */
        -o-transform: scale(1.5, 1); /* Opera */
        -webkit-transform: scale(1.5, 1); /* Safari And Chrome */
        transform: scale(1.5, 1); /* Standard Property */
      }
    }

    .icon-navbar {
        margin-right: 5px;
    }

    @media only screen and (max-width: 600px) {
        .izumi-logo-container {
            width: unset;

            .izumi-logo {
                margin-left: 0;
            }
        }
    }

    ::v-deep button.btn-show-emp-name {
        width: 150px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
