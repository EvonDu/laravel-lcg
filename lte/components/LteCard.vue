<script setup>
const props = defineProps({
    type: {
        type: String,
        default: "primary",
    },
    icon: {
        type: String,
        default: "",
    },
    title: {
        type: String,
        default: "",
    },
    outline: {
        type: Boolean,
        default: false,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
    collapse: {
        type: Boolean,
        default: false,
    },
    maximize: {
        type: Boolean,
        default: false,
    },
    remove: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="card card-primary" :class="{
        'card-outline': outline,
        'card-info': type==='info',
        'card-danger': type==='danger',
        'card-primary': type==='primary',
        'card-success': type==='success',
        'card-warning': type==='warning',
        'card-secondary': type==='secondary',
        'collapsed-card': collapsed,
    }">
        <div class="card-header" v-if="title || collapse || maximize || remove">
            <h3 class="card-title"><i :class="icon" v-if="icon"/> {{ title }}</h3>
            <div class="card-tools" v-if="collapse || maximize || remove">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse" v-if="collapse">
                    <i class="fas fa-plus" v-if="collapsed"></i>
                    <i class="fas fa-minus" v-else></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize" title="Maximize" v-if="maximize">
                    <i class="fas fa-expand"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove" v-if="remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <slot></slot>
        </div>
        <div class="card-footer" v-if="$slots.footer">
            <slot name="footer"></slot>
        </div>
    </div>
</template>
