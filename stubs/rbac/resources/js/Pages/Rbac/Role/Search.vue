<script setup>
//导入
import axios from 'axios';
import {reactive} from 'vue'
import {ElMessage} from 'element-plus'

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
    search: {},
    sort: { prop: null, order: null},
    paginate: { page: 1, size: 20, total: 0 },
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
const handleSearchSubmit = function(option){
    //参数定义
    var params = {};

    //字段参数
    for (var key in option?.search){
        params[key] = option.search[key];
    }

    //分页参数
    var paginate = Object.assign({}, data.paginate, option?.paginate);
    if(paginate?.page > 1)
        params["page"] = paginate.page;
    if(paginate?.size !== 20)
        params["size"] = paginate.size;

    //排序参数
    var sort = Object.assign({}, data.sort, option?.sort);
    if(option?.sort?.prop)
        params["sort"] = sort?.prop;
    if(option?.sort?.order)
        params["order"] = (sort.order === "descending") ? "desc" : "asc";

    //触发事件
    emit('searching');

    //执行请求
    axios.get(props.api, {params: params}).then(function (response) {
        //执行回调
        if(typeof(data.callback) === "function")
            data.callback(response.data?.data, response.data?.paginate);
    }).catch(function (error) {
        //显示错误
        ElMessage({ type: 'error', grouping: true, message: error.message })
    }).finally(function (){
        //保存选项
        data.sort = sort;
        data.paginate = paginate;
        //触发事件
        emit('searched');
        //关闭窗口
        data.show = false;
    });
}
</script>

<template>
    <lte-modal v-model="data.show" title="搜索" size="lg">
        <el-form label-width="100px">
            <el-form-item :label='labels["id"]'><el-input v-model='data.search.id'></el-input></el-form-item>
            <el-form-item :label='labels["name"]'><el-input v-model='data.search.name'></el-input></el-form-item>
        </el-form>
        <template #footer>
            <el-button type="default" @click="data.show = false">取消</el-button>
            <el-button type="primary" @click="handleSearchSubmit({search:data.search})">确认</el-button>
        </template>
    </lte-modal>
</template>

