<template>
	<div class="card">
		<div class="card-body">
			<div class="header">
				<div class="header__straight" />
				<div class="header__text">
					<slot>
						<span>Header Page</span>
					</slot>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
    name: 'VHeaderPage',
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    .card {
        border: 1px solid $mystic;
        box-shadow: 0 0 37px rgba(8,21,66,0.05);
        -webkit-box-shadow: 0 0 37px rgba(8,21,66,0.05);

        .header {
            display: flex;

            &__straight {
                width: 5px;
                line-height: 1rem;
                background-color: $west-side;
            }

            &__text {
                font-size: 20px;
                margin-left: 10px;

                font-weight: bold;
            }
        }
    }
</style>
