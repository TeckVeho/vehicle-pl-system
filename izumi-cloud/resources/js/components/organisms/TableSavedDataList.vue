<template>
	<b-table
		:id="id"
		:class="[className, 'v-table']"
		:fields="fields"
		:items="items"
		:current-page="currentPage"
		show-empty
		striped
		hover
		bordered
		responsive
	>
		<template #head(file)>
			<i class="fas fa-download" />
		</template>

		<template #cell(file)="data">
			<span @click="goToDownload(data.item.id)">{{ $t('DATA_LIST_DETAIL_DOWNLOAD') }}</span>
		</template>

		<template #empty="">
			<span>{{ $t('TABLE_EMPTY') }}</span>
		</template>
	</b-table>
</template>

<script>
export default {
    name: 'TableSavedDataList',
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
    },
    methods: {
        goToDownload(id = -1) {
            if (
                (!id)
            ) {
                this.$bus.emit('doDownloadFile', -1);
            } else {
                this.$bus.emit('doDownloadFile', id);
            }
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    .v-table {
        border: 1px solid $mercury;
        ::v-deep thead {
            th {
                text-align: center;
                background-color: $tolopea;
                color: $white;
                min-width: 150px;
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

            td.path {
                span {
                    text-decoration: underline;
                    cursor: pointer;
                }
            }
        }
    }
</style>
