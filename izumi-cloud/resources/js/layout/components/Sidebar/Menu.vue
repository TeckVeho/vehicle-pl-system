<template>
	<div v-show="toggole">
		<ul class="display-menu" :style="toggole ? `position: fixed;` : 'position: relative'">
			<router-link v-for="(itemRouter, indexRouter) in routes" :key="indexRouter" :to="itemRouter.path">
				<li :class="'item ' + 'router-item-' + indexRouter + 1">
					<div>
						<i :class="itemRouter.meta.icon" :style="`font-size: ${isMobile ? '35px' : '50px'}`" />
						<span :style="`font-size: ${isMobile ? '14px' : '16px'}`">{{ $t(itemRouter.meta.title) }}</span>
					</div>

					<ul class="sub-menu">
						<template v-for="sub in itemRouter['children']">
							<template v-if="!sub.hidden">
								<router-link :key="`sub-menu-${sub['meta']['title']}`" :to="`${itemRouter.path}/${sub['path']}`">
									<span>{{ $t(sub["meta"]["title"]) }}</span>
								</router-link>
							</template>
						</template>
					</ul>
				</li>
			</router-link>
		</ul>
	</div>
</template>

<script>
export default {
    name: 'MenuSidebar',
    props: {
        routes: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
        },
        toggole: {
            type: Boolean,
            require: true,
            default: true,
        },
    },
    data() {
        return {
            isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
        };
    },
    created() { },
};
</script>

<style lang="scss" scoped>
@import "@/scss/variables.scss";

.display-menu {
    height: 100%;
    width: 130px;
    text-align: center;
    margin-block-end: 0;
    margin-inline-end: 0;
    list-style-type: disc;
    margin-block-start: 0;
    margin-inline-start: 0;
    padding-inline-start: 0;
    z-index: 10000 !important;

    a {
        width: 130px;
        display: block;

        li {
            display: inline-flex;

            i {
                padding: 25px;
                display: block;
                margin-bottom: 5px;
            }

            color: $white;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 20px;
            text-decoration: none;
            list-style-type: none;
        }

        ul {
            display: none;
        }

        &:hover {
            text-decoration: none;

            ul {
                padding: 0;
                left: 130px;
                display: block;
                position: absolute;
                background-color: $tolopea;
                z-index: 10000 !important;

                a {
                    width: 200px;
                    display: inline-flex;

                    span {
                        width: 100%;
                        color: $white;
                        text-align: left;
                        padding: 8px 10px;
                    }

                    &:hover {
                        background-color: $west-side;
                    }
                }
            }
        }
    }

    a.router-link-active {
        li {
            color: $west-side;
        }
    }
}
</style>
