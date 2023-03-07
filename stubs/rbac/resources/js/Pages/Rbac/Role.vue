<script setup>
//导入
import axios from 'axios';
import {reactive, computed, getCurrentInstance} from 'vue'
import {ElMessage, ElMessageBox} from 'element-plus'
import {Plus, Search, Refresh} from '@element-plus/icons-vue'

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
    title: "Role",
    search: {},
    models: [],
    loading: false,
    keyword: "",
    sort: { prop: null, order: null},
    paginate: { page: 1, size: 20, total: 0 },
});
//方法
const handleAdd = function(){
    instance.refs.refAdd.open({});
};
const handleEdit = function(model){
    instance.refs.refEdit.open(model);
};
const handleView = function(model){
    instance.refs.refView.open(model);
};
const handleDelete = function(model){
    ElMessageBox.confirm('此操作将永久删除该数据, 是否继续?', '提示', {confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning'})
        .then(() => {
            handleDeleteSubmit(model);
        })
        .catch(() => {

        });
};
const handleSearch = function(){
    instance.refs.refSearch.open(data.search);
};
const handleRefresh = function(){
    handleSearchSubmit({});
};
const handleAddSubmit = function(model){
    axios.post(`${props.api}`, model, {header : {'Content-Type': 'application/json'}}).then(function (response) {
        ElMessage({ type: 'success', grouping: true, message: '添加成功' });
        instance.refs.refAdd.close();
        handleSearchSubmit({});
    }).catch(function(response) {
        if(response.response.data.errors)
            instance.refs.refAdd.setErrors(response.response.data.errors);
        if(response.response.data.message)
            ElMessage({ type: 'error', grouping: true, message: response.response.data.message })
    });
};
const handleEditSubmit = function(model){
    axios.put(`${props.api}/${model.id}`, model, {header : {'Content-Type': 'application/json'}}).then(function (response) {
        ElMessage({ type: 'success', grouping: true, message: '修改成功' });
        instance.refs.refEdit.close();
        handleSearchSubmit({});
    }).catch(function(response) {
        if(response.response.data.errors)
            instance.refs.refEdit.setErrors(response.response.data.errors);
        if(response.response.data.message)
            ElMessage({ type: 'error', grouping: true, message: response.response.data.message })
    });
};
const handleDeleteSubmit = function(model){
    axios.delete(`${props.api}/${model.id}`).then(function (response) {
        ElMessage({ type: 'success', grouping: true, message: '删除成功' });
        handleSearchSubmit({});
    }).catch(function(response) {
        ElMessage({ type: 'error', grouping: true, message: '删除失败' })
    });
};
const handleSearchSubmit = function(options){
    //params
    var params = {};
    //search
    var search;
    if(options.keyword){
        search = {name : options.keyword};
    } else {
        search = options.search ? options.search : data.search;
    }
    for (var key in search){
        params[key] = search[key];
    }
    //paginate
    var paginatePage = options?.paginate?.page ? options?.paginate?.page : data.paginate.page;
    var paginateSize = options?.paginate?.size ? options?.paginate?.size : data.paginate.size;
    if(paginatePage > 1)
        params["page"] = paginatePage;
    if(paginateSize !== 20)
        params["size"] = paginateSize;
    //sort
    var sortProp = options?.sort?.prop !== undefined ? options?.sort?.prop : data.sort.prop;
    var sortOrder = options?.sort?.order !== undefined ? options?.sort?.order : data.sort.order;
    if(sortProp)
        params["sort"] = sortProp;
    if(sortOrder)
        params["order"] = (sortOrder === "descending") ? "desc" : "asc";
    //request
    data.loading = true;
    axios.get(props.api, {params: params}).then(function (response) {
        data.models = response.data.data;
        data.sort.prop = sortProp;
        data.sort.order = sortOrder;
        data.paginate.page = response.data.paginate.page;
        data.paginate.size = response.data.paginate.size;
        data.paginate.total = response.data.paginate.total;
    }).catch(function (error) {
        ElMessage({ type: 'error', grouping: true, message: error.message })
    }).finally(function (){
        data.loading = false;
        data.search = search;
        instance.refs.refSearch.close();
    });
};
const handlePermissions = function(model){
    instance.refs.refPermissionsTree.setCheckedKeys(model.permissions,false);
    instance.refs.refPermissions.open(model);
};
const handlePermissionsSubmit = function(model){
    let selected = instance.refs.refPermissionsTree.getCheckedKeys(false);
    selected = selected.filter(function (s) { return s && s.trim() });
    model.permissions  = selected;
    handleEditSubmit(model);
    instance.refs.refPermissions.close();
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
                            <el-button color="#007bff" @click="handleSearch" :icon="Search">搜索</el-button>
                            <el-button color="#626aef" @click="handleRefresh" :icon="Refresh">刷新</el-button>
                        </el-button-group>
                    </el-col>
                    <el-col :xs="0" :sm="12">
                        <el-input placeholder="Please input" class="input-with-select" v-model="data.keyword" @keydown.enter="handleSearchSubmit({keyword: data.keyword})">
                            <template #append>
                                <el-button type="primary" :icon="Search" @click="handleSearchSubmit({keyword: data.keyword})"/>
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
                <el-table-column label="操作" width="215px">
                    <template #default="scope">
                        <el-button-group size="small">
                            <el-button type="info" @click="handleView(scope.row)">详情</el-button>
                            <el-button type="success" @click="handleEdit(scope.row)">编辑</el-button>
                            <el-button type="primary" @click="handlePermissions(scope.row)">权限</el-button>
                            <el-button type="danger" @click="handleDelete(scope.row)">删除</el-button>
                        </el-button-group>
                    </template>
                </el-table-column>
            </lte-grid>
            <!-- 查看 -->
            <lte-window ref="refView" title="详情" v-slot="slot">
                <el-descriptions size="large" :column="2" border>
                    <el-descriptions-item :label='labels["id"]'><span v-text='slot.model.id'/></el-descriptions-item>
                    <el-descriptions-item :label='labels["name"]'><span v-text='slot.model.name'/></el-descriptions-item>
                    <el-descriptions-item :label='labels["permissions"]'><span v-text='slot.model.permissions'/></el-descriptions-item>
                    <el-descriptions-item :label='labels["created_at"]'><span v-text='slot.model.created_at'/></el-descriptions-item>
                    <el-descriptions-item :label='labels["updated_at"]'><span v-text='slot.model.updated_at'/></el-descriptions-item>
                </el-descriptions>
            </lte-window>
            <!-- 添加 -->
            <lte-window ref="refAdd" title="添加" v-slot="slot" :existSubmit="true" @submit="handleAddSubmit">
                <el-form label-width="100px">
                    <el-form-item :label='labels["name"]' :error='slot.errors.name'><el-input v-model='slot.model.name'></el-input></el-form-item>
                </el-form>
            </lte-window>
            <!-- 编辑 -->
            <lte-window ref="refEdit" title="编辑" v-slot="slot" :existSubmit="true" @submit="handleEditSubmit">
                <el-form label-width="100px">
                    <el-form-item :label='labels["name"]' :error='slot.errors.name'><el-input v-model='slot.model.name'></el-input></el-form-item>
                </el-form>
            </lte-window>
            <!-- 搜索 -->
            <lte-window ref="refSearch" title="搜索" v-slot="slot" :existSubmit="true" @submit="handleSearchSubmit({search:$event})">
                <el-form label-width="100px">
                    <el-form-item :label='labels["id"]'><el-input v-model='slot.model.id'></el-input></el-form-item>
                    <el-form-item :label='labels["name"]'><el-input v-model='slot.model.name'></el-input></el-form-item>
                    <el-form-item :label='labels["permissions"]'><el-input v-model='slot.model.permissions'></el-input></el-form-item>
                </el-form>
            </lte-window>
            <!-- 权限 -->
            <lte-window ref="refPermissions" title="权限" v-slot="slot" :existSubmit="true" @submit="handlePermissionsSubmit">
                <el-tree ref="refPermissionsTree" node-key="code" :data="nodeList" show-checkbox default-expand-all/>
            </lte-window>
        </lte-card>
    </lte-layout>
</template>