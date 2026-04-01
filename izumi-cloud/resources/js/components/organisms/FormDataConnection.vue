<template>
	<div class="form-data-connection">
		<!-- FINAL TRANSFER TIME -->
		<div class="form-item">
			<vLabel
				:text-label="$t('DATA_CONNECTION_LIST_FINAL_TRANSFER_TIME')"
				:class-name="'form-item__final-transfer-time'"
			/>
			<vInput
				:value="dataForm.final_data_connection ? dataForm.final_data_connection : ''"
				:type="'text'"
				:placeholder="''"
				:readonly="true"
			/>
		</div>

		<!-- CONNECTION DATE NAME -->
		<div class="form-item">
			<vLabel
				:text-label="$t('DATA_CONNECTION_LIST_CONNECTION_DATA_NAME')"
				:class-name="'form-item__connection-date-name'"
			/>
			<vInput
				:value="dataForm.connection_data_name ? dataForm.connection_data_name : ''"
				:type="'text'"
				:placeholder="''"
				:readonly="true"
			/>
		</div>

		<!-- FROM -->
		<div class="form-item">
			<vLabel
				:text-label="$t('DATA_CONNECTION_LIST_FROM')"
				:class-name="'form-item__from'"
			/>
			<vInput
				:value="dataForm.from ? dataForm.from : ''"
				:type="'text'"
				:placeholder="''"
				:readonly="true"
			/>
		</div>

		<!-- TO -->
		<div class="form-item">
			<vLabel
				:text-label="$t('DATA_CONNECTION_LIST_TO')"
				:class-name="'form-item__to'"
			/>
			<vInput
				:value="dataForm.to ? dataForm.to : ''"
				:type="'text'"
				:placeholder="''"
				:readonly="true"
			/>
		</div>

		<!-- ACTIVE PASSIVE -->
		<div class="form-item">
			<vLabel
				:text-label="$t('DATA_CONNECTION_LIST_ACTIVE_PASSIVE')"
				:class-name="'form-item__active-passive'"
			/>
			<vInput
				:value="status_final"
				:type="'text'"
				:placeholder="''"
				:readonly="true"
			/>
		</div>

		<div v-if="dataForm.status_final === 'active'" class="form-item-label">
			<vLabel
				:text-label="$t('DATA_CONNECTION_LIST_FORCE_CONNECTION_DATA')"
				:class-name="'form-item__force_connection_data'"
				@click.native="handleClickForce()"
			/>
		</div>

		<!-- CONNECTION FREQUENCY -->
		<div class="form-item">
			<vLabel
				:text-label="$t('DATA_CONNECTION_LIST_CONNECTION_FREQUENCY')"
				:class-name="'form-item__connection-frequency'"
			/>
			<vInput
				:value="dataForm.connection_fequency ? dataForm.connection_fequency : ''"
				:type="'text'"
				:placeholder="''"
				:readonly="true"
			/>
		</div>

		<!-- CONNECTION TIMING -->
		<div class="form-item">
			<vLabel
				:text-label="$t('DATA_CONNECTION_LIST_CONNECTION_TIMING')"
				:class-name="'form-item__connection-timing'"
			/>
			<vInput
				:value="dataForm.connection_timing ? dataForm.connection_timing : ''"
				:type="'text'"
				:placeholder="''"
				:readonly="true"
			/>
		</div>

		<!-- STATUS -->
		<div class="form-item">
			<vLabel
				:text-label="$t('DATA_CONNECTION_LIST_STATUS')"
				:class-name="'form-item__status'"
			/>
			<vInput
				:value="status"
				:type="'text'"
				:placeholder="''"
				:readonly="true"
			/>
		</div>

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
				<span>{{ $t('DATA_CONNECTION_LIST_DETAIL_MODAL_BODY') }}</span>
			</template>

			<template #modal-footer>
				<b-button class="modal-btn" @click="handleBack()">{{ $t('DATA_CONNECTION_LIST_DETAIL_MODAL_FOOTER_BACK') }}</b-button>

				<b-button class="modal-btn" @click="handleConfirm()">{{ $t('DATA_CONNECTION_LIST_DETAIL_MODAL_FOOTER_EXCULDE') }}</b-button>
			</template>

		</b-modal>
	</div>
</template>

<script>
import vLabel from '@/components/atoms/vLabel';
import vInput from '@/components/atoms/vInput';

export default {
    name: 'FormDataConnection',
    components: {
        vLabel,
        vInput,
    },
    props: {
        dataForm: {
            type: Object,
            require: true,
            default: function() {
                return {
                    final_data_connection: '',
                    connection_data_name: '',
                    from: '',
                    to: '',
                    status_final: '',
                    connection_fequency: '',
                    connection_timing: '',
                    status: '',
                };
            },
        },
    },
    data() {
        return {
            showModal: false,
            status_final: '',
            status: '',

        };
    },
    computed: {
        statusFinalVal() {
            return this.dataForm.status_final;
        },
        statusVal() {
            return this.dataForm.status;
        },
    },
    watch: {
        statusFinalVal(status_final) {
            switch (status_final) {
            case 'active':
            case 'passive': {
                this.status_final = this.$t(`ACTIVE.${status_final}`);
                break;
            }

            default: {
                this.status_final = '';
                break;
            }
            }
        },
        statusVal(status) {
            switch (status) {
            case 'success':
            case 'fail':
            case 'excluding':
            case 'waiting': {
                this.status = this.$t(`STATUS.${status}`);
                break;
            }

            default: {
                this.status = '';
                break;
            }
            }
        },
    },
    methods: {
        handleClickForce() {
            this.showModal = true;
        },
        handleBack() {
            this.showModal = false;
        },
        handleConfirm() {
            this.$bus.emit('DataConnectionDetailClickModalConfirm');
            this.showModal = false;
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/scss/variables.scss';

.form-data-connection {
    .form-item {
        margin-bottom: 10px;
    }

    .form-item-label {
        label {
            margin-top: 10px;
            margin-bottom: 20px;

            text-decoration: underline;
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
