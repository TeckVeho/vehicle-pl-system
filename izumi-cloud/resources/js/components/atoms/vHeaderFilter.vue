<template>
	<div class="header-filter">
		<h6>
			<slot name="label-filter">
				<span id="collapsed-show-hide-filter" v-b-toggle.zone-filter>
					<i class="fas fa-angle-up when-open" />
					<i class="fas fa-angle-down when-closed" />
					<span class="filter-title">{{ $t('FILTER') }}</span>
				</span>
			</slot>
		</h6>

		<div class="show-filter">
			<b-collapse id="zone-filter">
				<slot name="zone-filter" />
			</b-collapse>
		</div>
	</div>
</template>

<script>
export default {
    name: 'VHeaderFilter',
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    .filter-title:hover {
        cursor: pointer;
    }

    .header-filter {
        h6 {
            width: 100%;
            text-indent: 10%;
            border-bottom: 2px solid $black;
            line-height: 0.1em;
            margin: 10px 0 20px;
            font-size: 16px;

            span {
                background:$white;
                padding: 0 10px;

                font-weight: bold;

                i {
                    line-height: unset;
                }
            }
        }

        .show {
            padding-bottom: 17px;
            border-bottom: 2px solid $black;
        }
    }

    .collapsed > .when-open,
    .not-collapsed > .when-closed {
        display: none;
    }
</style>
