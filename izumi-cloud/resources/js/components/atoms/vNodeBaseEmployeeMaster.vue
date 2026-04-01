<template>
	<div :class="['node-employee-master', item.color]" @click="emitNodeClickEvent(item.color, item.id, item.name)">
		<div class="base-name">
			{{ item.name }}
		</div>
	</div>
</template>

<script>
export default {
    name: 'VNodeBaseEmployeeMaster',
    props: {
        item: {
            type: Object,
            required: true,
            default: () => {
                return {};
            },
        },
    },
    methods: {
        emitNodeClickEvent(CLASS_LIST, ID, DEPARTMENT) {
            this.$bus.emit('NODE_CLICK_EVENT', CLASS_LIST, ID, DEPARTMENT);
        },
    },
};
</script>

<style lang="scss" scoped>
    @import '@/scss/variables.scss';

    .node-employee-master {
        width: 100%;
        height: 100%;

        border: 1px solid $black;
        border-radius: 7px;

        cursor: pointer;

        display: flex;
        align-items: center;

        padding: 8px 5px;

        font-weight: 600;
        font-size: 15px;

        .base-name {
            display: flex;
            width: 100%;

            text-align: center;
            justify-content: center;

            word-break: break-word;
        }

        &:hover {
            border: 1px solid $red-berry;
        }
    }

    .affiliation-base {
        background-color: $sun;
    }

    .support-base {
        background-color: $baja-white;
    }

    .other-base {
        color: $white;
        background-color: $boulder;
    }
</style>
