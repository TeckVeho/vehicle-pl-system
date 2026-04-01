<template>
	<div class="title">
		<div class="title-body">
			<div class="header__text">
				<slot>
					<span>Title</span>
				</slot>
			</div>
		</div>
	</div>
</template>

<script>
export default {
    name: 'VTitle',
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

            .header__text {
                font-size: 20px;
                font-weight: 600;
                border-bottom: 2px solid black;
                padding-bottom: 8px;
            }
</style>
