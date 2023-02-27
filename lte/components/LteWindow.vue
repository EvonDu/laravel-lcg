<script setup>
import { reactive } from 'vue'

const props = defineProps({
    title: "",
    existSubmit: false,
});

const data = reactive({
    show: false,
    model: {},
    errors: {},
});

defineExpose({
    open : function(model){
        data.model = JSON.parse(JSON.stringify(model));
        data.show = true;
    },
    close : function(){
        data.show = false;
    },
    setErrors: function(errors){
        data.errors = [];
        for (let key in errors) {
            if(errors[key] instanceof Array)
                data.errors[key] = errors[key][0];
            else
                data.errors[key] = errors[key];
        }
    },
});
</script>

<template>
    <lte-modal v-model="data.show" :title="title" size="lg">
        <slot :model="data.model" :errors="data.errors">
        </slot>
        <template #footer v-if="existSubmit">
            <el-button type="default" @click="data.show = false">取消</el-button>
            <el-button type="primary" @click="$emit('submit', data.model)">确认</el-button>
        </template>
    </lte-modal>
</template>
