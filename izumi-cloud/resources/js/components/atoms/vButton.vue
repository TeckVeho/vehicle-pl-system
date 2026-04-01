<template>
	<b-button
		:class="className"
		:disabled="disabled"
	>
		<slot>
			{{ textButton }}
		</slot>

		<slot name="custom" />
	</b-button>
</template>

<script>
export default {
    name: 'VButton',
    props: {
        textButton: {
            type: String,
            require: false,
            default: '',
        },
        className: {
            type: String,
            require: false,
            default: 'v-button-default',
        },
        disabled: {
            type: Boolean,
            require: false,
            default: false,
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    .btn {
        min-width: 150px;
        border: none;

        font-weight: bold;

        &:focus {
            box-shadow: none;
        }

        &:active {
            box-shadow: none;
        }

        &:active {
            &:focus {
                box-shadow: none;
            }
        }
    }

    .btn-radius {
        min-width: 150px;
        float: right;
        margin-bottom: 10px;
        text-align: center;
        align-items: center;
    }

    .btn-radius-default {
        min-width: 150px;
        margin-bottom: 10px;
        text-align: center;
        align-items: center;
    }

    .btn-apply {
        min-width: 185px;
        background: $west-side;
        border: none;
    }

    .v-button-select-data {
        background: $white;
        min-width: 250px;
        min-height: 50px;
        box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
    }

    .v-button-import-data {
        min-width: 200px;
    }

    .v-button-default {
        background-color: $west-side !important;

        &:hover {
            opacity: .8;
            background-color: $west-side !important;
        }

        &:active {
            background-color: $west-side !important;
        }

        &:focus {
            background-color: $west-side !important;
        }
    }

    .v-button-primary {
        background-color: $picton-blue !important;

        &:hover {
            opacity: .8;
            background-color: $picton-blue !important;
        }

        &:active {
            background-color: $picton-blue !important;
        }

        &:focus {
            background-color: $picton-blue !important;
        }
    }
</style>
