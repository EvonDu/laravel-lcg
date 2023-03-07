<script setup>
//导入
import axios from 'axios';
import {computed, getCurrentInstance, reactive} from 'vue'
import {ElMessage} from 'element-plus'

//实例
const instance = getCurrentInstance();

//参数
const props = defineProps({
    api: String,
    labels: Object,
    permissions: Object,
});

//事件
const emit = defineEmits([
    'submit',
]);

//数据
const data = reactive({
    show: false,
    callback: null,
    model: {},
});

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

//接口
defineExpose({
    open : function(model, callback){
        data.model = JSON.parse(JSON.stringify(model));
        instance.refs.refPermissionsTree.setCheckedKeys(model.permissions,false);
        data.show = true;
        data.callback = callback;
    },
    close : function(){
        data.show = false;
    },
});

//函数
const handlePermissionsSubmit = function(){
    //获取选中
    let selected = instance.refs.refPermissionsTree.getCheckedKeys(false);
    selected = selected.filter(function (s) { return s && s.trim() });
    data.model.permissions  = selected;
    //提交修改
    axios.put(`${props.api}/${data.model.id}`, data.model).then(function (response) {
        //提示信息
        ElMessage({ type: 'success', grouping: true, message: '修改成功' });
        //关闭窗口
        data.show = false;
        //执行回调
        if(typeof(data.callback) === "function")
            data.callback(response.data?.data);
    }).catch(function(response) {
        //提示错误
        ElMessage({ type: 'error', grouping: true, message: response?.response?.data?.message });
    });
};
</script>

<template>
    <lte-modal v-model="data.show" title="权限" size="lg">
        <el-form label-width="100px">
            <el-tree ref="refPermissionsTree" node-key="code" :data="nodeList" show-checkbox default-expand-all/>
        </el-form>
        <template #footer>
            <el-button type="default" @click="data.show = false">取消</el-button>
            <el-button type="primary" @click="handlePermissionsSubmit">确认</el-button>
        </template>
    </lte-modal>
</template>

