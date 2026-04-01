<template>
	<b-input-group>
		<b-input-group-prepend is-text>
			<input v-model="isChecked" :size="checkboxSize" class="chk_filter" type="checkbox" :dusk="id">
		</b-input-group-prepend>
		<b-input-group-prepend is-text>
			<span style="min-width: 125px;">{{ textPrepend }}</span>
		</b-input-group-prepend>
		<vSelect
			:id="id"
			v-model="data"
			:name="name"
			:class="className"
			:style="styleEl"
			:data-options="dataOptions"
			:placeholder="placeholder"
			:disabled="!isChecked"
		/>
	</b-input-group>
</template>

<script>
import vSelect from './vSelect.vue';

export default {
    name: 'VSelectGroup',
    components: {
        vSelect,
    },
    props: {
        id: {
            type: String,
            require: false,
            default: '',
        },
        className: {
            type: String,
            require: false,
            default: 'v-custom-input',
        },
        styleEl: {
            type: String,
            require: false,
            default: '',
        },
        value: {
            type: [String, Number, Array, Object],
            require: true,
            default: null,
        },
        placeholder: {
            type: String,
            require: true,
            default: function() {
                return this.$t('PLEASE_SELECT');
            },
        },
        name: {
            type: String,
            require: false,
            default: '',
        },
        textPrepend: {
            type: String,
            require: false,
            default: '',
        },
        isCheck: {
            type: Boolean,
            require: true,
            default: function() {
                return false;
            },
        },
        dataOptions: {
            type: Array,
            require: true,
            default: function() {
                return [
                    { value: null, text: this.$t('PLEASE_SELECT') },
                ];
            },
        },
        checkboxSize: {
            type: String,
            require: false,
            default: 'sm',
        },
    },
    data() {
        return {
            isChecked: false,
            data: '',
        };
    },
    computed: {
        dataChange() {
            return this.data;
        },
        isCheckedChange() {
            return this.isChecked;
        },
        setDataChange() {
            return this.value;
        },
    },
    watch: {
        dataChange() {
            this.setEmit();
        },
        isCheckedChange() {
            this.setEmitIsChecked();
        },
        setDataChange() {
            this.setData();
        },
        isCheck() {
            this.isChecked = this.isCheck;
        },
    },
    created() {
        this.setData();
        this.setEmit();
    },
    methods: {
        setData() {
            this.data = this.value;
        },
        setEmit() {
            this.$emit('input', this.data);
        },
        setEmitIsChecked() {
            this.$emit('isChecked', this.isChecked);
        },
    },
};
</script>
