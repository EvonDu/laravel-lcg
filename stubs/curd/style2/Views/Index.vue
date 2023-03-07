<script setup>
//导入
import axios from 'axios';
import {reactive, getCurrentInstance, onMounted} from 'vue'
import {ElMessage, ElMessageBox} from 'element-plus'
import {Plus, Search, Refresh} from '@element-plus/icons-vue'

import ModelSearch from './Search.vue';
import ModelCreate from './Create.vue';
import ModelUpdate from './Update.vue';
import ModelDetail from './Detail.vue';

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
    title: "__MODEL_NAME__",
    models: [],
    keyword: "",
    loading: false,
    sort: { prop: null, order: null},
    paginate: { page: 1, size: 20, total: 0 },
});
//方法
const handleCreate = function(){
    instance.refs.refCreate.open({}, function(model){
        handleRefresh();
    });
};
const handleUpdate = function(model){
    instance.refs.refUpdate.open(model, function(model){
        handleRefresh();
    });
};
const handleDetail = function(model){
    instance.refs.refDetail.open(model);
};
const handleDelete = function(model){
    ElMessageBox.confirm('此操作将永久删除该数据, 是否继续?', '提示', {confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning'})
        .then(() => {
            axios.delete(`${props.api}/${model.__MODEL_PK__}`).then(function (response) {
                ElMessage({ type: 'success', grouping: true, message: '删除成功' });
                handleRefresh();
            }).catch(function(response) {
                ElMessage({ type: 'error', grouping: true, message: '删除失败' })
            });
        })
        .catch(() => {

        });
};
const handlePermissions = function(model){
    instance.refs.refPermissions.open(model, function(model){
        handleRefresh();
    });
};
const handleSearch = function(option){
    instance.refs.refSearch.search(option, function(models, paginate){
        data.models = models;
        data.paginate = paginate;
    });
};
const handleRefresh = function(option){
    handleSearch({});
};
const handleAdvancedSearch = function(option){
    instance.refs.refSearch.open(function(models, paginate){
        data.models = models;
        data.paginate = paginate;
    });
};
//执行
onMounted(() => {
    handleSearch({});
})
</script>

<template>
    <lte-layout :lte="lte" :title="data.title" :breadcrumbs="breadcrumbs">
        <lte-card title="列表" icon="fas fa-list" collapse maximize>
            <!-- 功能 -->
            <div style="margin-bottom: 12px">
                <el-row>
                    <el-col :xs="24" :sm="12">
                        <el-button-group>
                            <el-button color="#00a65a" @click="handleCreate" :icon="Plus">新增</el-button>
                            <el-button color="#626aef" @click="handleRefresh" :icon="Refresh">刷新</el-button>
                            <el-button color="#007bff" @click="handleAdvancedSearch" :icon="Search">搜索</el-button>
                        </el-button-group>
                    </el-col>
                    <el-col :xs="0" :sm="12">
                        <el-input placeholder="Please input" class="input-with-select" v-model="data.keyword" @keydown.enter="handleSearch({search: {name: data.keyword}})">
                            <template #append>
                                <el-button type="primary" :icon="Search" @click="handleSearch({search: {name: data.keyword}})"/>
                            </template>
                        </el-input>
                    </el-col>
                </el-row>
            </div>
            <!-- 表格 -->
            <lte-grid :models="data.models" :loading="data.loading"
                :paginatePage="data.paginate.page" :paginateSize="data.paginate.size" :paginateTotal="data.paginate.total"
                @sort-change="handleSearch({sort: $event})"
                @page-change="handleSearch({paginate: $event})">
                __TABLE_ITEMS__
                <el-table-column label="操作" width="167px">
                    <template #default="scope">
                        <el-button-group size="small">
                            <el-button type="info" @click="handleDetail(scope.row)">详情</el-button>
                            <el-button type="success" @click="handleUpdate(scope.row)">编辑</el-button>
                            <el-button type="danger" @click="handleDelete(scope.row)">删除</el-button>
                        </el-button-group>
                    </template>
                </el-table-column>
            </lte-grid>
        </lte-card>
        <!-- 子页面 -->
        <ModelSearch ref="refSearch" :labels="props.labels" :api="props.api" @searching="data.loading=true" @searched="data.loading=false"></ModelSearch>
        <ModelCreate ref="refCreate" :labels="props.labels" :api="props.api"></ModelCreate>
        <ModelUpdate ref="refUpdate" :labels="props.labels" :api="props.api"></ModelUpdate>
        <ModelDetail ref="refDetail" :labels="props.labels" :api="props.api"></ModelDetail>
    </lte-layout>
</template>
