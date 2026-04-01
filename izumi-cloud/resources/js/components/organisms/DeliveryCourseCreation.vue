<template>
	<div class="delivery-course-creation">
		<template v-if="listSelected.length === 0">
			<b-row>
				<b-col>
					<b-input-group>
						<b-form-select v-model="tempSelected" :options="items" :disabled="!isEdit">
							<template #first>
								<option :value="null" disabled>{{ $t('COURSE_MASTER_CREATE_PLEASE_SELECT') }}</option>
							</template>
						</b-form-select>
						<b-input-group-append v-if="tempSelected">
							<b-button variant="success" @click="onClickAdd()">
								<i class="far fa-plus-circle icon-handle" />
							</b-button>
						</b-input-group-append>
					</b-input-group>
				</b-col>
			</b-row>
		</template>

		<template v-if="listSelected.length !== 0">
			<template v-for="(item, idx) in listSelected">
				<div :key="`item-${idx + 1}`" class="item-select">
					<b-row>
						<b-col>
							<b-input-group>
								<b-form-select :value="item" :options="items" disabled />
								<b-input-group-append v-if="isEdit">
									<b-button variant="danger" @click="onClickDelete(item)">
										<i class="fas fa-trash icon-handle" />
									</b-button>
								</b-input-group-append>
							</b-input-group>
						</b-col>
					</b-row>
				</div>
			</template>

			<b-row v-if="isEdit">
				<b-col>
					<b-input-group>
						<b-form-select v-model="tempSelected">
							<template #first>
								<option :value="null" disabled>{{ $t('COURSE_MASTER_CREATE_PLEASE_SELECT') }}</option>
							</template>
							<template v-for="(item, idx) in items">
								<template v-if="!item.disabled">
									<b-form-select-option
										:key="`item-${idx + 1}`"
										:value="item.value"
									>
										{{ item.text }}
									</b-form-select-option>
								</template>
							</template>
						</b-form-select>
						<b-input-group-append v-if="tempSelected">
							<b-button variant="success" @click="onClickAdd()">
								<i class="far fa-plus-circle icon-handle" />
							</b-button>
						</b-input-group-append>
					</b-input-group>
				</b-col>
			</b-row>
		</template>
	</div>
</template>

<script>
export default {
    name: 'DeliveryCourseCreation',
    props: {
        items: {
            type: Array,
            default: () => [],
            required: true,
            validate: (value) => {
                return Array.isArray(value);
            },
        },
        listSelected: {
            type: Array,
            default: () => [],
            required: true,
            validate: (value) => {
                return Array.isArray(value);
            },
        },
        isEdit: {
            type: Boolean,
            default: false,
            required: true,
        },
    },
    data() {
        return {
            tempSelected: null,
        };
    },
    methods: {
        onClickAdd() {
            this.$emit('add', this.tempSelected);
            this.tempSelected = null;
        },
        onClickDelete(id) {
            this.$emit('delete', id);
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables';

    .delivery-course-creation {
        .button-handle {
            button {
                height: 38px;
                .icon-handle {
                    vertical-align: middle;

                    font-size: 20px;

                    cursor: pointer;
                }
            }
        }

        .item-select {
            margin-bottom: 10px;
        }
    }
</style>
