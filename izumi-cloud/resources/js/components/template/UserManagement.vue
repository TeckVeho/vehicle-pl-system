<!-- eslint-disable vue/html-indent -->
<template>
    <div class="user-management">
        <!-- HEADER CONTAINER -->
        <vHeaderPage class="user-management__title-header">{{ $t('PAGE_TITLE.USER_MANAGEMENT') }}</vHeaderPage>

        <!-- FILTER FIELD -->
        <UserManagementFilter class="user-management__filter" />

        <!-- USER MANAGEMENT TABLE -->
        <TableUserManagement
            :id="'table-user-management'"
            :fields="vFields"
            :items="vItems"
            :path="'/master-manager'"
        />
    </div>
</template>

<script>
import vHeaderPage from '@/components/atoms/vHeaderPage';
// import vButton from '@/components/atoms/vButton';
import UserManagementFilter from '@/components/organisms/UserManagementFilter';
import TableUserManagement from '@/components/organisms/TableUserManagement';

export default {
    name: 'UserManagementTemplate',
    components: {
        vHeaderPage,
        // vButton,
        UserManagementFilter,
        TableUserManagement,
    },
    props: {
        vFields: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
            validate: (value) => {
                return typeof value === 'object';
            },
        },
        vItems: {
            type: Array,
            require: true,
            default: function() {
                return [];
            },
            validate: (value) => {
                return typeof value === 'object';
            },
        },
    },
    methods: {
        doRegistration() {
            this.$router.push(
                {
                    path: `/master-manager/user-master-create`,
                },
                (onAbort) => {}
            );
        },
    },
};
</script>

<style lang="scss" scoped>
	.user-management {
		overflow: hidden;

		min-height: calc(100vh - 89px);

		&__registration-field {
			margin: 0;
			width: 100%;
		}

		&__title-header,
		&__filter {
			margin-bottom: 20px;
		}

		.item-per-page {
			width: 100px;
		}

		&__pagination {
			display: flex;
			justify-content: center;
		}
	}
</style>

