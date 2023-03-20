<script setup>
//导入
import axios from 'axios';
import {reactive} from 'vue'
import {ElMessage} from 'element-plus'

//参数
const props = defineProps({
    api: String,
    labels: Object,
});

//数据
const data = reactive({
    show: false,
    callback: null,
    model: {},
    errors: {},
});

//接口
defineExpose({
    open : function(model, callback){
        data.model = JSON.parse(JSON.stringify(model));
        data.show = true;
        data.callback = callback;
    },
    close : function(){
        data.show = false;
    },
});

//函数
const handleAddSubmit = function(){
    axios.post(`${props.api}`, data.model).then(function (response) {
        //提示信息
        ElMessage({ type: 'success', grouping: true, message: '添加成功' });
        //关闭窗口
        data.show = false;
        //执行回调
        if(typeof(data.callback) === "function")
            data.callback(response.data?.data);
    }).catch(function(response) {
        //设置错误
        if(response.response.data.errors)
            data.errors = response.response.data.errors;
        //显示信息
        ElMessage({ type: 'error', grouping: true, message: response?.response?.data?.message })
    });
};
</script>

<template>
    <lte-modal v-model="data.show" title="添加" size="lg">
        <el-form label-width="100px" @keydown.enter="handleAddSubmit" @submit.native.prevent>
            <el-form-item :label='labels["name"]' :error='data?.errors?.name?.[0]'><el-input v-model='data.model.name'></el-input></el-form-item>
        </el-form>
        <template #footer>
            <el-button type="default" @click="data.show = false">取消</el-button>
            <el-button type="primary" @click="handleAddSubmit">确认</el-button>
        </template>
    </lte-modal>
</template>

