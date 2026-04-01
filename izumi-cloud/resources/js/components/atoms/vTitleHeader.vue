<template>
	<div :class="['title-header', className]">
		<h6>
			<slot name="label-filter">
				<span id="title">
					<span>{{ $t(label) }}</span>
				</span>
			</slot>
		</h6>
	</div>
</template>

<script>
export default {
    name: 'TitleHeader',
    props: {
        label: {
            type: String,
            default: '',
        },
        className: {
            type: String,
            default: '',
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    .title-header {
        h6 {
            width: 100%;
            text-indent: 10%;
            border-bottom: 2px solid $tolopea;
            line-height: 0.1em;
            margin: 10px 0 20px;
            font-size: 16px;

            span {
                background: $white;
                padding: 0 10px;

                color: $tolopea;

                font-weight: bold;
            }
        }
    }
</style>
