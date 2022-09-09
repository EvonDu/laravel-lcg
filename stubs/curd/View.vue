<script setup>
//导入
import axios from 'axios';
import { ref, reactive } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Delete, Edit, Search, View, Share, Upload, Refresh } from '@element-plus/icons-vue'
//属性
const props = defineProps({
    api: String,
    lte: Object,
    labels: Object,
    breadcrumbs: Array,
});
//数据
const title = ref("__MODEL_NAME__");
const list = reactive({
    models: [],
    loading: false,
    order: { prop: null, type: null},
    paginate: { page: 1, size: 20, total: 0 },
});
const form = reactive({
    show: false,
    title: "",
    model: {},
    errors: {},
});
const detail = reactive({
    show: false,
    model: {},
});
const search = reactive({
    show: false,
    text: "",
    temp: {},
    model: {},
});
//方法
const handlePage = function(page){
    list.paginate.page = page;
    handleSearch();
};
const handleSort = function(order){
    list.order.prop = order.prop;
    list.order.type = order.order;
    handleSearch();
};
const handleAdd = function(){
    form.title = "新增";
    form.show = true;
    form.type = "CREATE";
    form.model = {};
    form.errors = {};
};
const handleView = function(model){
    detail.model = model;
    detail.show = true;
};
const handleEdit = function(model){
    form.title = "编辑";
    form.show = true;
    form.type = "UPDATE";
    form.model = {...model};
    form.errors = {};
};
const handleDelete = function(model){
    ElMessageBox.confirm('此操作将永久删除该数据, 是否继续?', '提示', {confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning'})
        .then(() => {
            axios.delete(props.api + '/' + model.__MODEL_PK__).then(function (response) {
                ElMessage({ type: 'success', grouping: true, message: '删除成功' });
                handleSearch();
            }).catch(function(response) {
                ElMessage({ type: 'error', grouping: true, message: '删除失败' })
            });
        }).catch(() => {});
};
const handleSubmit = function(model){
    if(form.type === "CREATE"){
        axios.post(props.api, model, {header : {'Content-Type': 'application/json'}}).then(function (response) {
            ElMessage({ type: 'success', grouping: true, message: '添加成功' });
            form.show = false;
            handleSearch();
        }).catch(function(response) {
            if(response.response.data.errors)
                form.errors = response.response.data.errors;
            if(response.response.data.message)
                ElMessage({ type: 'error', grouping: true, message: response.response.data.message })
        });
    } else {
        axios.put(props.api + '/' + model.__MODEL_PK__, model, {header : {'Content-Type': 'application/json'}}).then(function (response) {
            ElMessage({ type: 'success', grouping: true, message: '修改成功' });
            form.show = false;
            handleSearch();
        }).catch(function(response) {
            if(response.response.data.errors)
                form.errors = response.response.data.errors;
            if(response.response.data.message)
                ElMessage({ type: 'error', grouping: true, message: response.response.data.message })
        });
    }
};
const handleSearch = function(){
    list.loading = true;

    var params = JSON.parse(JSON.stringify(search.model))
    if(list.paginate.page > 1)
        params["page"] = list.paginate.page;
    if(list.order.prop)
        params["orderBy"] = list.order.prop;
    if(list.order.type)
        params["orderType"] = list.order.type;

    axios.get(props.api, {params: params}).then(function (response) {
        list.paginate.page = response.data.paginate.page;
        list.paginate.size = response.data.paginate.size;
        list.paginate.total = response.data.paginate.total;
        list.models = response.data.data;
        list.loading = false;
    }).catch(function (error) {
        alert(error);
    });
};
const handleSearchForm = function(){
    search.show = true;
    search.temp = JSON.parse(JSON.stringify(search.model))
};
const handleSearchFormSubmit = function(){
    search.show = false;
    search.model = search.temp;
    handleSearch();
};
const handleSearchSimpleSubmit = function(){
    search.model = {"id" : search.text};
    handleSearch();
};
//执行
handleSearch();
</script>

<template>
    <lte-layout :lte="lte" :title="title" :breadcrumbs="breadcrumbs">
        <!-- 表格 -->
        <lte-card title="列表" icon="fas fa-list" collapse maximize>
            <el-row>
                <el-col :xs="24" :sm="12">
                    <el-button-group>
                        <el-button color="#00a65a" @click="handleAdd()" :icon="Plus">新增</el-button>
                        <el-button color="#626aef" @click="handleSearch()" :icon="Refresh">刷新</el-button>
                        <el-button color="#007bff" @click="handleSearchForm" :icon="Search">搜索</el-button>
                    </el-button-group>
                </el-col>
                <el-col :xs="0" :sm="12">
                    <el-input placeholder="Please input" class="input-with-select" v-model="search.text" @keydown.enter="handleSearchSimpleSubmit()">
                        <template #append>
                            <el-button type="primary" :icon="Search" @click="handleSearchSimpleSubmit()"/>
                        </template>
                    </el-input>
                </el-col>
            </el-row>
            <br>
            <el-row>
                <el-table :data="list.models" v-loading="list.loading" style="width: 100%" @sort-change="handleSort">
                    __TABLE_ITEMS__
                    <el-table-column label="操作" width="167px">
                        <template #default="scope">
                            <el-button-group size="small">
                                <el-button type="info" @click="handleView(scope.row)">详情</el-button>
                                <el-button type="success" @click="handleEdit(scope.row)">编辑</el-button>
                                <el-button type="danger" @click="handleDelete(scope.row)">删除</el-button>
                            </el-button-group>
                        </template>
                    </el-table-column>
                </el-table>
            </el-row>
            <br>
            <el-row v-if="list.models">
                <el-col :xs="0" :sm="8">
                    <div class="lte-counted">
                        第 <b>{{(list.paginate.page-1) * list.paginate.size + 1}}-{{Math.min((list.paginate.page) * list.paginate.size + 1, list.paginate.total)}}</b> 条，共 <b>{{list.paginate.total}}</b> 条数据.
                    </div>
                </el-col>
                <el-col :xs="24" :sm="16">
                    <div class="lte-pagination">
                        <el-pagination background layout="prev,pager,next" :total="list.paginate.total" :page-size="list.paginate.size" :currentPage="list.paginate.page" @current-change="handlePage($event)"/>
                    </div>
                </el-col>
            </el-row>
        </lte-card>
        <!-- 表单 -->
        <lte-modal v-model="form.show" :title="form.title" size="lg">
            <el-form label-width="100px">
                __FORM_ITEMS__
            </el-form>
            <template #footer>
                <el-button type="default" @click="form.show = false">取消</el-button>
                <el-button type="primary" @click="handleSubmit(form.model)">确认</el-button>
            </template>
        </lte-modal>
        <!-- 视图 -->
        <lte-modal v-model="detail.show" title="详情" size="lg">
            <el-descriptions size="large" :column="2" border>
                __DETAIL_ITEMS__
            </el-descriptions>
        </lte-modal>
        <!-- 搜索 -->
        <lte-modal v-model="search.show" title="搜索" size="lg">
            <el-form label-width="100px">
                __SEARCH_ITEMS__
            </el-form>
            <template #footer>
                <el-button type="default" @click="search.show = false">取消</el-button>
                <el-button type="primary" @click="handleSearchFormSubmit()">搜索</el-button>
            </template>
        </lte-modal>
    </lte-layout>
</template>
