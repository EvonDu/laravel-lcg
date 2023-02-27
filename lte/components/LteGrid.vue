<script setup>
import { computed } from 'vue'

const props = defineProps({
    models: {type: Array, default: []},
    loading: {type: Boolean, default: false},
    sortProp: {type: String, default: null},
    sortOrder: {type: String, default: null},
    paginatePage: {type: Number, default: 1},
    paginateSize: {type: Number, default: 20},
    paginateTotal: {type: Number, default: 0},
});

const emit = defineEmits([
    'sort-change',
    'page-change',
])

const sortChange = function($event){
    emit('sort-change', $event);
}
const pageChange = function($event){
    emit('page-change', {page : $event, size : props.size});
}

const itemFirst = computed(() => {
    return (props.paginatePage-1) * props.paginateSize + 1
})
const itemLast = computed(() => {
    return Math.min((props.paginatePage) * props.paginateSize + 1, props.paginateTotal)
})
</script>

<style>
    .lte-curl-table{
        width: 100%;
    }
    .lte-curl-table .el-loading-mask{
        z-index: 1000;
    }
    .lte-curl-table .lte-curl-table-pagination{
       margin-top: 12px;
    }
</style>

<template>
    <div class="lte-curl-table">
        <el-row>
            <el-table :data="props.models" v-loading="props.loading" @sort-change="sortChange">
                <slot></slot>
            </el-table>
        </el-row>
        <el-row v-if="props.models" class="lte-curl-table-pagination">
            <el-col :xs="0" :sm="8">
                <div class="lte-counted">
                    第 <b>{{itemFirst}}-{{itemLast}}</b> 条，共 <b>{{paginateTotal}}</b> 条数据.
                </div>
            </el-col>
            <el-col :xs="24" :sm="16">
                <div class="lte-pagination">
                    <el-pagination background layout="prev,pager,next" :total="paginateTotal" :page-size="paginateSize" :currentPage="paginatePage" @current-change="pageChange"/>
                </div>
            </el-col>
        </el-row>
    </div>
</template>
