<script setup>
//导入
import axios from 'axios';
import {inject, reactive, computed, getCurrentInstance} from 'vue'
import {ElMessage, ElMessageBox} from 'element-plus'
import {Plus, Search, Refresh} from '@element-plus/icons-vue'

//挂载
const $lcg = inject('LCG');
//实例
const instance = getCurrentInstance();
//属性
const props = defineProps({
    api: String,
    lte: Object,
    info: Object,
    labels: Object,
    permissions: Object,
    breadcrumbs: Array,
});
//数据
const data = reactive({
    title: "角色",
    search: {},
    models: [],
    loading: false,
    keyword: "",
    sort: { prop: null, order: null},
    paginate: { page: 1, size: 20, total: 0 },
});
//方法
const handleAdd = function(){
    instance.refs.refAdd.open({model: {}});
};
const handleEdit = function(model){
    instance.refs.refEdit.open({model: JSON.parse(JSON.stringify(model))});
};
const handleView = function(model){
    instance.refs.refView.open({model: JSON.parse(JSON.stringify(model))});
};
const handleDelete = function(model){
    ElMessageBox.confirm('此操作将永久删除该数据, 是否继续?', '提示', {confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning'})
        .then(() => {
            handleDeleteSubmit({model: JSON.parse(JSON.stringify(model))});
        })
        .catch(() => {

        });
};
const handleSearch = function(){
    instance.refs.refSearch.open({model: data.search});
};
const handleRefresh = function(){
    handleSearchSubmit({});
};
const handleAddSubmit = function(data){
    let model = data.model;
    axios.post(`${props.api}`, model, {header : {'Content-Type': 'application/json'}}).then(function (response) {
        ElMessage({ type: 'success', grouping: true, message: '添加成功' });
        instance.refs.refAdd.close();
        handleSearchSubmit({});
    }).catch(function(response) {
        if(response.response.data.errors)
            instance.refs.refAdd.data().errors = response.response.data.errors;
        if(response.response.data.message)
            ElMessage({ type: 'error', grouping: true, message: response.response.data.message })
    });
};
const handleEditSubmit = function(data){
    let model = data.model;
    axios.put(`${props.api}/${model.id}`, model, {header : {'Content-Type': 'application/json'}}).then(function (response) {
        ElMessage({ type: 'success', grouping: true, message: '修改成功' });
        instance.refs.refEdit.close();
        handleSearchSubmit({});
    }).catch(function(response) {
        if(response.response.data.errors)
            instance.refs.refEdit.data().errors = response.response.data.errors;
        if(response.response.data.message)
            ElMessage({ type: 'error', grouping: true, message: response.response.data.message })
    });
};
const handleDeleteSubmit = function(data){
    let model = data.model;
    axios.delete(`${props.api}/${model.id}`).then(function (response) {
        ElMessage({ type: 'success', grouping: true, message: '删除成功' });
        handleSearchSubmit({});
    }).catch(function(response) {
        ElMessage({ type: 'error', grouping: true, message: '删除失败' })
    });
};
const handleSearchSubmit = function(options){
    let search = $lcg.getSearchOptions(options, {model:data.search, paginate:data.paginate, sort:data.sort});
    data.loading = true;
    axios.get(props.api, {params: search.params}).then(function (response) {
        data.models = response.data.data;
        data.sort = search.sort;
        data.paginate = response.data.paginate;
    }).catch(function (error) {
        ElMessage({ type: 'error', grouping: true, message: error.message })
    }).finally(function (){
        data.loading = false;
        data.search = search.model;
        instance.refs.refSearch.close();
    });
};
const handlePermissions = function(model){
    instance.refs.refPermissionsTree.setCheckedKeys(model.permissions,false);
    instance.refs.refPermissions.open({model: JSON.parse(JSON.stringify(model))});
};
const handlePermissionsSubmit = function(data){
    let selected = instance.refs.refPermissionsTree.getCheckedKeys(false);
    selected = selected.filter(function (s) { return s && s.trim() });
    data.model.permissions  = selected;
    handleEditSubmit(data);
    instance.refs.refPermissions.close();
};
const handleAssigns = function(model){
    let data = {
        model: JSON.parse(JSON.stringify(model)),
        search_input: "",
        search_items: [],
    }
    instance.refs.refAssigns.open(data, function(data){
        handleAssignsLoad()
    });
};
const handleAssignsLoad = function(){
    let data = instance.refs.refAssigns.data();
    data.loading = true;
    axios.get(`${props.api}/${data.model.id}/users`).then(function (response) {
        data.model.roleUsers = response.data.data;
    }).finally(function (){
        data.loading = false;
    });
};
const handleAssignsSearch = function(){
    let data = instance.refs.refAssigns.data();
    axios.get(`${props.api}/${data.model.id}/search`, {params: {"k": data.search_input}}).then(function (response) {
        let items = [];
        for(let item of response.data.data) {
            items.push({
                label : item.email,
                value : item.email,
            });
        }
        data.search_items = items;
    });
};
const handleAssignsAdd = function(){
    let data = instance.refs.refAssigns.data();
    axios.post(`${props.api}/${data.model.id}/users`, {email: data.search_input}).then(function (response) {
        ElMessage({ type: 'success', grouping: true, message: '添加成功' });
    }).catch(function(){
        ElMessage({ type: 'error', grouping: true, message: '添加失败' });
    }).finally(function (){
        data.search_input = "";
        handleAssignsLoad();
    });
};
const handleAssignsRemove = function(item){
    let data = instance.refs.refAssigns.data();
    axios.delete(`${props.api}/${data.model.id}/users/${item.id}`).then(function (response) {
        ElMessage({ type: 'success', grouping: true, message: '移除成功' });
    }).finally(function (){
        handleAssignsLoad();
    });
};
const handleAssignsSubmit = function(data){
    instance.refs.refAssigns.close();
};
//计算
const nodeList = computed(() => {
    let result = [];
    for (let i=0; i<props.permissions?.length; i++){
        let module = {
            label : props.permissions[i]?.module,
            children : [],
        };
        for (let j=0; j<props.permissions[i]?.items?.length; j++){
            let item = {
                code : props.permissions[i]?.items[j]?.code,
                label: props.permissions[i]?.items[j]?.name,
            }
            module.children.push(item);
        }
        result.push(module);
    }
    return result;
})
//执行
handleSearchSubmit({});
</script>

<template>
    <lte-layout :lte="lte" :title="data.title" :breadcrumbs="breadcrumbs">
        <lte-card title="列表" icon="fas fa-list" collapse maximize>
            <!-- 功能 -->
            <div style="margin-bottom: 12px">
                <el-row>
                    <el-col :xs="24" :sm="12">
                        <el-button-group>
                            <el-button color="#00a65a" @click="handleAdd" :icon="Plus">新增</el-button>
                            <el-button color="#626aef" @click="handleRefresh" :icon="Refresh">刷新</el-button>
                            <el-button color="#007bff" @click="handleSearch" :icon="Search">搜索</el-button>
                        </el-button-group>
                    </el-col>
                    <el-col :xs="0" :sm="12">
                        <el-input placeholder="请输入角色名" class="input-with-select" v-model="data.keyword" @keydown.enter="handleSearchSubmit({model:{name:data.keyword}})">
                            <template #append>
                                <el-button type="primary" :icon="Search" @click="handleSearchSubmit({model:{name:data.keyword}})"/>
                            </template>
                        </el-input>
                    </el-col>
                </el-row>
            </div>
            <!-- 表格 -->
            <lte-grid :models="data.models" :loading="data.loading"
                      :paginatePage="data.paginate.page" :paginateSize="data.paginate.size" :paginateTotal="data.paginate.total"
                      @sort-change="handleSearchSubmit({sort: $event})"
                      @page-change="handleSearchSubmit({paginate: $event})">
                <el-table-column prop='id' :label='labels["id"]' sortable='custom' show-overflow-tooltip></el-table-column>
                <el-table-column prop='name' :label='labels["name"]' sortable='custom' show-overflow-tooltip></el-table-column>
                <el-table-column prop='created_at' :label='labels["created_at"]' sortable='custom' show-overflow-tooltip></el-table-column>
                <el-table-column prop='updated_at' :label='labels["updated_at"]' sortable='custom' show-overflow-tooltip></el-table-column>
                <el-table-column label="操作" width="260px">
                    <template #default="scope">
                        <el-button-group size="small">
                            <el-button type="info" @click="handleView(scope.row)">详情</el-button>
                            <el-button type="success" @click="handleEdit(scope.row)">编辑</el-button>
                            <el-button type="primary" @click="handleAssigns(scope.row)">委派</el-button>
                            <el-button type="warning" @click="handlePermissions(scope.row)">权限</el-button>
                            <el-button type="danger" @click="handleDelete(scope.row)">删除</el-button>
                        </el-button-group>
                    </template>
                </el-table-column>
            </lte-grid>
            <!-- 查看 -->
            <lte-window ref="refView" title="详情" v-slot="window">
                <el-descriptions size="large" :column="2" border v-if="window.data.model">
                    <el-descriptions-item :label='labels["id"]'><span v-text='window.data.model.id'/></el-descriptions-item>
                    <el-descriptions-item :label='labels["name"]'><span v-text='window.data.model.name'/></el-descriptions-item>
                    <el-descriptions-item :label='labels["permissions"]'><span v-text='window.data.model.permissions'/></el-descriptions-item>
                    <el-descriptions-item :label='labels["created_at"]'><span v-text='window.data.model.created_at'/></el-descriptions-item>
                    <el-descriptions-item :label='labels["updated_at"]'><span v-text='window.data.model.updated_at'/></el-descriptions-item>
                </el-descriptions>
            </lte-window>
            <!-- 添加 -->
            <lte-window ref="refAdd" title="添加" v-slot="window" :existSubmit="true" @submit="handleAddSubmit">
                <el-form label-width="100px" v-if="window.data.model" @keydown.enter="handleAddSubmit(window.data)" @submit.native.prevent>
                    <el-form-item :label='labels["name"]' :error='window?.data?.errors?.name?.[0]'><el-input v-model='window.data.model.name'></el-input></el-form-item>
                </el-form>
            </lte-window>
            <!-- 编辑 -->
            <lte-window ref="refEdit" title="编辑" v-slot="window" :existSubmit="true" @submit="handleEditSubmit">
                <el-form label-width="100px" v-if="window.data.model" @keydown.enter="handleEditSubmit(window.data)" @submit.native.prevent>
                    <el-form-item :label='labels["name"]' :error='window?.data?.errors?.name?.[0]'><el-input v-model='window.data.model.name'></el-input></el-form-item>
                </el-form>
            </lte-window>
            <!-- 搜索 -->
            <lte-window ref="refSearch" title="搜索" v-slot="window" :existSubmit="true" @submit="handleSearchSubmit({model:$event.model})">
                <el-form label-width="100px" v-if="window.data.model" @keydown.enter="handleSearchSubmit({model:$event.model})" @submit.native.prevent>
                    <el-form-item :label='labels["id"]'><el-input v-model='window.data.model.id'></el-input></el-form-item>
                    <el-form-item :label='labels["name"]'><el-input v-model='window.data.model.name'></el-input></el-form-item>
                </el-form>
            </lte-window>
            <!-- 权限 -->
            <lte-window ref="refPermissions" title="权限" v-slot="window" :existSubmit="true" @submit="handlePermissionsSubmit">
                <el-tree ref="refPermissionsTree" node-key="code" :data="nodeList" show-checkbox default-expand-all/>
            </lte-window>
            <!-- 委派 -->
            <lte-window ref="refAssigns" title="委派" v-slot="window" :existSubmit="true" @submit="handleAssignsSubmit">
                <el-form label-width="100px" v-if="window.data.model">
                    <div class="option">
                        <el-select-v2
                            v-model="window.data.search_input"
                            filterable
                            remote
                            :remote-method="handleAssignsSearch"
                            :options="window.data.search_items"
                            placeholder="请输入邮箱搜索用户"
                        />
                        <el-button type="success" :icon="Plus" @click="handleAssignsAdd()">添加</el-button>
                    </div>
                    <el-table :data="window.data.model.roleUsers" v-if="window.data.model.roleUsers" style="width: 100%">
                        <el-table-column label="用户" prop="name"/>
                        <el-table-column label="邮箱" prop="email"/>
                        <el-table-column label="操作">
                            <template #default="scope">
                                <el-button-group size="small">
                                    <el-button type="danger" @click="handleAssignsRemove(scope.row)">移除</el-button>
                                </el-button-group>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-form>
            </lte-window>
        </lte-card>
    </lte-layout>
</template>

<style scoped>
.option{
    margin-bottom: 12px;
}
.option *{
    margin-right: 6px;
}
</style>
