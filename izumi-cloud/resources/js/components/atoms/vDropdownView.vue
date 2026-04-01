<template>
	<div class="dropdown-content">
		<b-row lg="1" md="1" sm="1">
			<b-col id="data-dropdown" cols="12">
				<h6>
					<slot name="label-dropdown-content">
						<span id="collapsed-show-hide-dropdown-content dropdownTitle" v-b-toggle.dropdown-content>
							<slot>{{ $t(dropdownTitle[0]) }}</slot>
						</span>
					</slot>
				</h6>

				<div class="show-dropdown-content">
					<b-collapse id="dropdown-content" visible>
						<slot name="dropdown-content" />
					</b-collapse>
				</div>
			</b-col>

			<b-col id="data-dropdown" cols="12">
				<h6>
					<slot name="label-dropdown-content">
						<span id="collapsed-show-hide-dropdown-content dropdownTitle" v-b-toggle.dropdown-content-1>
							<slot>{{ $t(dropdownTitle[1]) }}</slot>
						</span>
					</slot>
				</h6>

				<div class="show-dropdown-content">
					<b-collapse id="dropdown-content-1" visible>
						<slot name="dropdown-content-1" />
					</b-collapse>
				</div>
			</b-col>

			<b-col id="data-dropdown" cols="12">
				<h6>
					<slot name="label-dropdown-content">
						<span id="collapsed-show-hide-dropdown-content dropdownTitle" v-b-toggle.dropdown-content-2>
							<slot>{{ $t(dropdownTitle[2]) }}</slot>
						</span>
					</slot>
				</h6>

				<div class="show-dropdown-content">
					<b-collapse id="dropdown-content-2" visible>
						<slot name="dropdown-content-2" />
					</b-collapse>
				</div>
			</b-col>

			<b-col id="maintenance-data-dropdown" cols="12">
				<h6>
					<slot name="label-dropdown-content">
						<span id="collapsed-show-hide-dropdown-content dropdownTitle" v-b-toggle.dropdown-content-3>
							<slot>{{ $t(dropdownTitle[3]) }}</slot>
						</span>
					</slot>
				</h6>

				<div class="show-dropdown-content">
					<b-collapse id="dropdown-content-3" visible>
						<slot name="dropdown-content-3" />
					</b-collapse>
				</div>
			</b-col>

			<b-col v-if="dropdownTitle[4]" id="maintenance-data-dropdown" cols="12">
				<h6>
					<slot name="label-dropdown-content">
						<span id="collapsed-show-hide-dropdown-content dropdownTitle" v-b-toggle.dropdown-content-4>
							<slot>{{ $t(dropdownTitle[4]) }}</slot>
						</span>
					</slot>
				</h6>

				<div class="show-dropdown-content">
					<b-collapse id="dropdown-content-4" visible>
						<slot name="dropdown-content-4" />
					</b-collapse>
				</div>
			</b-col>
		</b-row>
	</div>
</template>

<script>
export default {
    name: 'VDropdownView',
    props: {
        dropdownTitle: {
            type: Array,
            required: true,
            default: () => {
                return ['', '', '', ''];
            },
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables.scss';

.dropdown-content {
    width: 90%;
    margin: 0 auto;

    h6 {
        width: 100%;
        color: #0F0448;
        text-indent: 5%;
        font-size: 16px;
        font-weight: bold;
        line-height: 0.1em;
        margin: 10px 0 20px;
        border-bottom: 1px solid #0F0448;

        span {
            background: $white;
            padding: 0 10px;

            i {
                line-height: unset;
            }
        }
    }

    h6:hover {
        cursor: pointer;
    }

    .show {
        padding-bottom: 17px;
    }
}

.collapsed>.when-open,
.not-collapsed>.when-closed {
    display: none;
}
</style>
