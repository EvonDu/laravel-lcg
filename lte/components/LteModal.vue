<template>
    <div class="modal fade" @click.self="close" ref="modal">
        <div class="modal-dialog" :class="{
            'modal-dialog-scrollable': scrollable,
            'modal-sm': size === 'sm',
            'modal-lg': size === 'lg',
            'modal-xl': size === 'xl',
        }">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ title }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <slot></slot>
                </div>
                <div class="modal-footer justify-content-between" v-if="$slots.footer">
                    <slot name="footer"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "LteModal",
    props: {
        modelValue : {
            type: Boolean,
            default() {
                return false;
            }
        },
        title: {
            type: String,
            default() {
                return "标题";
            }
        },
        scrollable: {
            type: Boolean,
            default() {
                return false;
            }
        },
        size: {
            type: String,
            default() {
                return "";
            }
        }
    },
    emits: ['update:modelValue', 'submit'],
    data() {
        return {
            el: null,
            modal: null,
        }
    },
    watch: {
        modelValue (new_val) {
            if(new_val){
                this.onShow()
            } else {
                this.onClose()
            }
        },
    },
    methods: {
        close: function(){
            this.$emit('update:modelValue', false);
        },
        onShow: function(){
            this.getModal().show();
        },
        onClose: function(){
            this.getModal().hide();
        },
        getModal: function(){
            if(this.modal)
                return this.modal;
            else {
                this.modal = new bootstrap.Modal(this.$refs.modal, {});
                return this.modal;
            }
        }
    },
}
</script>
