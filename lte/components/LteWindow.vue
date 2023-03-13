<script setup>
import { reactive } from 'vue'

const props = defineProps({
    title: "",
    existSubmit: false,
});

const data = reactive({
    show: false,
    data: {},
});

defineExpose({
    open: function(data_window, fn_opened){
        data.show = true;
        data.data = data_window;
        if(typeof(fn_opened) === "function")
            fn_opened(data.data);
    },
    close: function(){
        data.show = false;
    },
    data: function(){
        return data.data;
    }
});
</script>

<template>
    <lte-modal v-model="data.show" :title="title" size="lg">
        <slot :data="data.data">
        </slot>
        <template #footer v-if="existSubmit">
            <el-button type="default" @click="data.show = false">取消</el-button>
            <el-button type="primary" @click="$emit('submit', data.data)">确认</el-button>
        </template>
    </lte-modal>
</template>
