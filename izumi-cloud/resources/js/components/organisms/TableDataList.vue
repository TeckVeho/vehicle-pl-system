<template>
	<b-table
		:id="id"
		hover
		striped
		bordered
		show-empty
		responsive
		:items="items"
		:fields="fields"
		:no-sort-reset="true"
		:no-local-sorting="true"
		:current-page="currentPage"
		:class="[className, 'v-table']"
		@sort-changed="handleSort"
	>
		<template #head(result)>
			<span>{{ $t('DRIVER_RECORDER.TABLE_DETAIL') }}</span>
		</template>

		<template #cell(from)="data">
			<span>{{ data.item.from ? data.item.from : '' }}</span>
		</template>

		<template #cell(to)="data">
			<span>{{ data.item.to ? data.item.to : '' }}</span>
		</template>

		<template #cell(remark)="data">
			<span>{{ convertStringWithLength(data.item.remark, 10) }}</span>
		</template>

		<template #cell(result)="result">
			<i class="fas fa-eye" @click="goToDetail(result.item.id)" />
			<!-- <span class="btn-detail" @click="goToDetail(result.item.id)">{{ $t('DATA_LIST_DETAIL') }}</span> -->
		</template>

		<template #empty="">
			<span>{{ $t('TABLE_EMPTY') }}</span>
		</template>
	</b-table>
</template>

<script>
import { convertStringWithLength } from '@/utils/convertStringToDot';

export default {
    name: 'TableDataList',
    props: {
        id: {
            type: String,
            require: false,
            default: '',
            validate: value => {
                return value;
            },
        },
        className: {
            type: String,
            require: false,
            default: '',
            validate: value => {
                return value;
            },
        },
        fields: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
            validate: value => {
                value.every(e => typeof e === 'object');
            },
        },
        items: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
            validate: value => {
                value.every(e => typeof e === 'object');
            },
        },
        currentPage: {
            type: Number,
            require: true,
            default: 1,
            validate: value => {
                return value > 0;
            },
        },
        path: {
            type: String,
            require: true,
            default: '',
            validate: value => {
                return value;
            },
        },
    },
    data() {
        return {
            convertStringWithLength,
            filterQuery: {
                order_column: '',
                order_type: '',
            },
        };
    },
    methods: {
        goToDetail(value) {
            this.$router.push({ path: `${this.path}/${value}` });
        },
        handleSort(ctx) {
            this.filterQuery.order_column = ctx.sortBy === 'data_id' ? 'data_id' : ctx.sortBy;
            this.filterQuery.order_column = ctx.sortBy === 'data_name' ? 'data_name' : ctx.sortBy;
            this.filterQuery.order_column = ctx.sortBy === 'from' ? 'from' : ctx.sortBy;

            this.filterQuery.order_type = ctx.sortDesc ? 'desc' : 'asc';

            this.$bus.emit('sendFilterQueryDataList', this.filterQuery);
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables.scss';

::-webkit-scrollbar {
  height: 3px;
}

::-webkit-scrollbar-thumb {
  border-radius: 45px;
}

.v-table {
	border: 1px solid $mercury;

	::v-deep thead {
		th {
			text-align: center;
			background-color: $tolopea;
			color: $white;
			min-width: 150px;
			vertical-align: middle;
		}
	}

	::v-deep tbody {
		tr {
			&:hover {
				background-color: $west-side;

				td {
					color: $white;
				}
			}
		}

		td {
			text-align: center;
			color: $shark;
			vertical-align: middle;
		}

		td.result {
			span {
				text-decoration: underline;
				cursor: pointer;
			}
		}
	}
}
</style>
