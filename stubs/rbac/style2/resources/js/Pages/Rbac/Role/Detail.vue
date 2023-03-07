<script setup>
//导入
import {reactive} from 'vue'

//属性
const props = defineProps({
    labels: Object,
});

//数据
const data = reactive({
    show: false,
    model: {},
});

//接口
defineExpose({
    open : function(model){
        data.model = JSON.parse(JSON.stringify(model));
        data.show = true;
    },
    close : function(){
        data.show = false;
    },
});
</script>

<template>
    <lte-modal v-model="data.show" title="编辑" size="lg">
        <el-descriptions size="large" :column="2" border>
            <el-descriptions-item :label='labels["id"]'><span v-text='data.model.id'/></el-descriptions-item>
            <el-descriptions-item :label='labels["name"]'><span v-text='data.model.name'/></el-descriptions-item>
            <el-descriptions-item :label='labels["permissions"]'><span v-text='data.model.permissions'/></el-descriptions-item>
            <el-descriptions-item :label='labels["created_at"]'><span v-text='data.model.created_at'/></el-descriptions-item>
            <el-descriptions-item :label='labels["updated_at"]'><span v-text='data.model.updated_at'/></el-descriptions-item>
        </el-descriptions>
    </lte-modal>
</template>
