<script setup>
//导入
import axios from 'axios';
import {reactive} from 'vue'
import {ElMessage} from 'element-plus'
import {Plus} from '@element-plus/icons-vue'

//参数
const props = defineProps({
    api: String,
});
//数据
const data = reactive({
    show: false,
    loading: false,
    model: {},
    users: [],
});
//搜索
const search = reactive({
    input: "",
    items: [],
});

//加载列表
const handleLoad = function(){
    //执行请求
    data.loading = true;
    axios.get(`${props.api}/${data.model.id}/users`).then(function (response) {
        data.users = response.data.data;
    }).finally(function (){
        data.loading = false;
    });
};
//搜索邮箱
const handSearch = function(){
    axios.get(`${props.api}/${data.model.id}/search`, {params: {"k": search.input}}).then(function (response) {
        search.items = [];
        for(var item of response.data.data) {
            search.items.push({
                label : item.email,
                value : item.email,
            });
        }
    });
}
//添加用户
const handleAdd = function(){
    axios.post(`${props.api}/${data.model.id}/users`, {email: search.input}).then(function (response) {
        ElMessage({ type: 'success', grouping: true, message: '添加成功' });
    }).catch(function(){
        ElMessage({ type: 'error', grouping: true, message: '添加失败' });
    }).finally(function (){
        search.input = "";
        handleLoad();
    });
}
//移除用户
const handleRemove = function(item){
    axios.delete(`${props.api}/${data.model.id}/users/${item.id}`).then(function (response) {
        ElMessage({ type: 'success', grouping: true, message: '移除成功' });
    }).finally(function (){
        handleLoad();
    });
}

//接口
defineExpose({
    open : function(model, callback){
        //赋值
        data.model = JSON.parse(JSON.stringify(model));
        data.show = true;
        //清除
        search.input = "";
        //加载
        handleLoad();
    },
    close : function(){
        data.show = false;
    },
});
</script>

<template>
    <lte-modal v-model="data.show" title="委派" size="lg">
        <el-form label-width="100px">
            <div class="option">
                <el-select-v2
                    v-model="search.input"
                    filterable
                    remote
                    :remote-method="handSearch"
                    :options="search.items"
                    placeholder="请输入邮箱搜索用户"
                />
                <el-button type="success" :icon="Plus" @click="handleAdd()">添加</el-button>
            </div>
            <el-table :data="data.users" style="width: 100%">
                <el-table-column label="用户" prop="name"/>
                <el-table-column label="邮箱" prop="email"/>
                <el-table-column label="操作">
                    <template #default="scope">
                        <el-button-group size="small">
                            <el-button type="danger" @click="handleRemove(scope.row)">移除</el-button>
                        </el-button-group>
                    </template>
                </el-table-column>
            </el-table>
        </el-form>
        <template #footer>
            <el-button type="default" @click="data.show = false">取消</el-button>
            <el-button type="primary" @click="data.show = false">确认</el-button>
        </template>
    </lte-modal>
</template>

<style scoped>
    .option{
        margin-bottom: 12px;
    }
    .option *{
        margin-right: 6px;
    }
</style>
