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
    <lte-modal v-model="data.show" title="详情" size="lg">
        <el-descriptions size="large" :column="2" border>
            __DETAIL_ITEMS__
        </el-descriptions>
    </lte-modal>
</template>
