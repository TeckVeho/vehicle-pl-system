<template>
	<b-table
		:id="id"
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

		<template #cell(type)="data">
			<span>{{ data.item.type ? $t(`ACTIVE.${data.item.type}`) : '' }}</span>
		</template>

		<template #cell(final_status)="data">
			<span>{{ data.item.final_status ? $t(`STATUS.${data.item.final_status}`) : '' }}</span>
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
export default {
    name: 'TableDataConnectionList',
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
            default: '/data-manager/data-connect/detail',
            validate: value => {
                return value;
            },
        },
    },
    data() {
        return {
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
            this.filterQuery.order_column = ctx.sortBy === 'final_transfer_time' ? 'final_transfer_time' : ctx.sortBy;
            this.filterQuery.order_column = ctx.sortBy === 'from' ? 'from' : ctx.sortBy;
            this.filterQuery.order_column = ctx.sortBy === 'active_passive' ? 'active_passive' : ctx.sortBy;
            this.filterQuery.order_column = ctx.sortBy === 'connection_frequency' ? 'connection_frequency' : ctx.sortBy;
            this.filterQuery.order_column = ctx.sortBy === 'connection_timing' ? 'connection_timing' : ctx.sortBy;
            this.filterQuery.order_column = ctx.sortBy === 'status' ? 'status' : ctx.sortBy;

            this.filterQuery.order_type = ctx.sortDesc ? 'desc' : 'asc';

            this.$bus.emit('sendFilterQueryDataConnection', this.filterQuery);
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
      min-width: 180px;
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
