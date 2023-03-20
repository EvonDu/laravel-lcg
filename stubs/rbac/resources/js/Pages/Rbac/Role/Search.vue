<script setup>
//导入
import axios from 'axios';
import {inject, reactive} from 'vue'
import {ElMessage} from 'element-plus'

//挂载
const $lcg = inject('LCG')

//属性
const props = defineProps({
    api: String,
    labels: Object,
});

//事件
const emit = defineEmits([
    'searching',
    'searched',
])

//数据
const data = reactive({
    show: false,
    callback: null,
    model: {},
    sort: { prop: null, order: null},
    paginate: { page: 1, size: 20 },
});

//接口
defineExpose({
    open : function(callback){
        data.show = true;
        data.callback = callback;
    },
    close : function(){
        data.show = false;
    },
    search(options, callback){
        data.callback = callback;
        handleSearchSubmit(options);
    }
});

//函数
const handleSearchSubmit = function(options){
    //查询参数
    let search = $lcg.getSearchOptions(options, {model:data.model, paginate:data.paginate, sort:data.sort});

    //触发事件
    emit('searching');

    //执行请求
    axios.get(props.api, {params: search.params}).then(function (response) {
        //执行回调
        if(typeof(data.callback) === "function")
            data.callback(response.data?.data, response.data?.paginate);
    }).catch(function (error) {
        //显示错误
        ElMessage({ type: 'error', grouping: true, message: error.message })
    }).finally(function (){
        //保存选项
        data.model = search.model;
        data.sort = search.sort;
        data.paginate = search.paginate;
        //触发事件
        emit('searched');
        //关闭窗口
        data.show = false;
    });
}
</script>

<template>
    <lte-modal v-model="data.show" title="搜索" size="lg">
        <el-form label-width="100px" @keydown.enter="handleSearchSubmit({model:data.model})" @submit.native.prevent>
            <el-form-item :label='labels["id"]'><el-input v-model='data.model.id'></el-input></el-form-item>
            <el-form-item :label='labels["name"]'><el-input v-model='data.model.name'></el-input></el-form-item>
        </el-form>
        <template #footer>
            <el-button type="default" @click="data.show = false">取消</el-button>
            <el-button type="primary" @click="handleSearchSubmit({model:data.model})">确认</el-button>
        </template>
    </lte-modal>
</template>

