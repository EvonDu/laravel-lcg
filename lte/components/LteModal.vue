<script setup>
import { Modal } from "bootstrap";
import { ref, watch, onMounted } from 'vue';

let modal = null;

const refModal = ref();

const props = defineProps({
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
});

const emits = defineEmits(['update:modelValue'])

const close = function(){
    emits('update:modelValue', false);
};

const onShow = function(){
    modal.show();
};

const onClose = function(){
    modal.hide();
};

watch(() => props.modelValue, (new_val) => {
    if(new_val){
        onShow()
    } else {
        onClose()
    }
});

onMounted(() => {
    //创建模态框
    modal = new Modal(refModal.value, {});
});
</script>

<template>
    <div class="modal fade" @click.self="close" ref="refModal">
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
