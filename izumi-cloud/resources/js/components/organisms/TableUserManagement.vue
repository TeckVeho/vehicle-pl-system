<!-- eslint-disable vue/html-indent -->
<template>
    <div>
        <b-table
            :id="id"
            :class="[className, 'v-table']"
            :fields="fields"
            :items="items"
            :no-sort-reset="true"
            striped
            show-empty
            responsive
            bordered
            :no-local-sorting="true"
            @sort-changed="handleSort"
        >
            <template #cell(role_name)="role_name">
            <span>
                {{ $t(toI18nKey(role_name.item.role_name)) }}
            </span>
            </template>

            <template #cell(edit)="edit">

            <span class="btn-function btn-edit" @click="goToEdit(edit.item.id)">
                <i class="fas fa-pen" />
            </span>
            </template>

            <template #cell(remove)="remove">
            <span class="btn-function btn-delete" @click="showModalDelete(remove.item.id)">
                <i class="fas fa-trash" />
            </span>
            </template>

            <template #empty="">
            <span>{{ $t('TABLE_EMPTY') }}</span>
            </template>
        </b-table>

        <b-modal
            id="modal-cf"
            v-model="showModal"
            no-close-on-backdrop
            no-close-on-esc
            hide-header
            :static="true"
            header-class="modal-custom-header"
            content-class="modal-custom-body"
            footer-class="modal-custom-footer"
        >
            <template #default>
            <span>{{ $t('DELETE_USER_CONFIRMATION', { employee_name: handleUser.name, user_id: handleUser.id }) }}</span>
            </template>

            <template #modal-footer>
            <b-button class="modal-btn btn-cancel" @click="showModal = false">{{ $t('NO') }}</b-button>

            <b-button class="modal-btn btn-apply" @click="handleDeleteUser()">{{ $t('YES') }}</b-button>
            </template>

        </b-modal>
    </div>
</template>

<script>
import { removeUser } from '@/api/modules/user';

const API_URLS = {
    urlRemoveOneUser: `/user/`,
};

export default {
    name: 'TableUserManagement',
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
            filterQuery: {
                order_column: '',
                order_type: '',
            },

            showModal: false,
            handleUser: {
                id: '',
                name: '',
            },
        };
    },
    methods: {
        goToEdit(value) {
            this.$router.push({ path: `/master-manager/user-master-edit/${value}` });
        },
        handleSort(ctx) {
            this.filterQuery.order_column = ctx.sortBy === 'user_role' ? 'user_role' : ctx.sortBy;
            this.filterQuery.order_column = ctx.sortBy === 'employee_name' ? 'employee_name' : ctx.sortBy;
            this.filterQuery.order_column = ctx.sortBy === 'user_id' ? 'user_id' : ctx.sortBy;

            this.filterQuery.order_type = ctx.sortDesc ? 'desc' : 'asc';

            this.$bus.emit('sendFilterQueryUserManagement', this.filterQuery);
        },
        showModalDelete(user_id) {
            this.showModal = true;
            const CURRENT_USER = this.items.filter(user => user.id === user_id)[0];

            this.handleUser.id = CURRENT_USER.id;
            this.handleUser.name = CURRENT_USER.name;
        },
        async handleDeleteUser() {
            this.showModal = false;

            await removeUser(API_URLS.urlRemoveOneUser + this.handleUser.id)
                .then((response) => {
                    if (response.code === 200) {
                        this.$bus.emit('refreshUserList');
                    }
                });
        },
        toI18nKey(string) {
            if (string) {
                switch (string) {
                case 'crew':
                    return 'USER_MANAGEMENT.ROLE.CREW';
                case 'clerks':
                    return 'USER_MANAGEMENT.ROLE.CLERKS';
                case 'tl':
                    return 'USER_MANAGEMENT.ROLE.TL';
                case 'department_office_staff':
                    return 'USER_MANAGEMENT.ROLE.DEPARTMENT_OFFICE_STAFF';
                case 'accounting':
                    return 'USER_MANAGEMENT.ROLE.ACCOUNTING';
                case 'general_affair':
                    return 'USER_MANAGEMENT.ROLE.GENERAL_AFFAIR';
                case 'personnel_labor':
                    return 'USER_MANAGEMENT.ROLE.PERSONNEL_LABOR';
                case 'headquarter':
                    return 'USER_MANAGEMENT.ROLE.HEADQUARTER';
                case 'am_sm':
                    return 'USER_MANAGEMENT.ROLE.AM_SM';
                case 'quality_control':
                    return 'USER_MANAGEMENT.ROLE.QUALITY_CONTROL';
                case 'sales':
                    return 'USER_MANAGEMENT.ROLE.SALES';
                case 'site_manager':
                    return 'USER_MANAGEMENT.ROLE.SITE_MANAGER';
                case 'hq_manager':
                    return 'USER_MANAGEMENT.ROLE.HQ_MANAGER';
                case 'executive_officer':
                    return 'USER_MANAGEMENT.ROLE.EXECUTIVE_OFFICER';
                case 'department_manager':
                    return 'USER_MANAGEMENT.ROLE.DEPARTMENT_MANAGER';
                case 'director':
                    return 'USER_MANAGEMENT.ROLE.DIRECTOR';
                case 'accountant_direction':
                    return 'USER_MANAGEMENT.ROLE.ACCOUNTANT_DIRECTION';
                case 'dx_user':
                    return 'USER_MANAGEMENT.ROLE.DX_USER';
                case 'dx_manager':
                    return 'USER_MANAGEMENT.ROLE.DX_MANAGER';
                default:
                    return `[${string}]`;
                }
            }
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    ::-webkit-scrollbar {
        height: 3px;
        width: 3px;
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

        .btn-function {
            text-decoration: underline;
            &:hover {
                cursor: pointer;
            }
        }

    }

    ::v-deep #modal-cf {
        .modal-custom-header {
            border-bottom: 0 none;
        }

        .modal-custom-body {
            text-align: center;
            padding-top: 60px;

            span {
                font-weight: 500;
            }
        }

        .modal-custom-footer {
            border-top: 0 none;
            justify-content: center;
            padding-top: 50px;

            button {
                border: none;
                min-width: 150px;
                font-weight: 500;
                margin: 0 15px;

                &:hover {
                    opacity: 0.8;
                }

                &:focus {
                    opacity: 0.8;
                }
            }

            .modal-btn {
                background-color: $west-side;
                color: $white;

                &:focus {
                    background-color: $west-side;
                    color: $white;
                }
            }
        }
    }
</style>
