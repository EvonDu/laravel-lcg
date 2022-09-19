<script setup>
import $ from 'jquery'
import defaultLogo from '../images/AdminLTELogo.png'
import defaultProfile from '../images/user-128x128.jpg'
//import Treeview from "admin-lte/build/js/Treeview"
import { onMounted } from 'vue'

const props = defineProps({
    name: {
        type: String,
        default() {
            return "AdminLTE"
        }
    },
    logo: {
        type: String,
        default() {
            return null
        }
    },
    user: {
        type: Object,
        default() {
            return {
                "name" : "Admin",
                "profile" : null
            }
        }
    },
    navs: {
        type: Array,
        default() {
            return [
                {
                    title: "Dashboard",
                    icon: "fas fa-tachometer-alt",
                    url: "#",
                    badge: "Demo",
                },
                {
                    title: "SYSTEM",
                    type: "header",
                },
                {
                    title: "List",
                    icon: "fas fa-th",
                    url: "#",
                    childList: [
                        {title: "Item1", url: "#"},
                        {title: "Item2", url: "#"},
                    ]
                },
            ]
        }
    },
});

onMounted(() => {
    //重新触发AdminLTE中TreeView的加载事件
    $(window).trigger("load.lte.treeview");
})

//处理导航栏当前活跃
let handleActive = function(list, url){
    for (let i in list){
        if(list[i].url === url){
            list[i].active = true;
            return true;
        }
        else if(list[i].childList){
            let isChildActive = handleActive(list[i].childList, url);
            if(isChildActive){
                list[i].active = true;
                return true;
            }
        }
    }
    return false;
}
handleActive(props.navs, location.href);
</script>

<template>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link">
            <img :src="logo" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8" v-if="logo">
            <img :src="defaultLogo" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8" v-else>
            <span class="brand-text font-weight-light">{{ name }}</span>
        </a>
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex" v-if="user">
                <div class="image">
                    <img :src="user.profile" class="img-circle elevation-2" alt="User Image" v-if="user.profile">
                    <img :src="defaultProfile" class="img-circle elevation-2" alt="User Image" v-else>
                </div>
                <div class="info" v-if="user.name">
                    <a href="#" class="d-block">{{ user.name }}</a>
                </div>
            </div>
            <!-- SidebarSearch Form -->
            <div class="form-inline">
                <div class="input-group" data-widget="sidebar-search">
                    <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-sidebar">
                            <i class="fas fa-search fa-fw"></i>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar nav-child-indent flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <template  v-for="nav in navs">
                        <li class="nav-header" v-if="nav.type === 'header'">{{ nav.title }}</li>
                        <li class="nav-item" :class="{'has-treeview': nav.childList, 'menu-is-opening': nav.active, 'menu-open': nav.active}" v-else>
                            <a :href="nav.url" class="nav-link" :class="{'active':nav.active}">
                                <i class="nav-icon" :class="nav.icon"></i>
                                <p>
                                    {{ nav.title }}
                                    <i class="right fas fa-angle-left" v-if="nav.childList"></i>
                                    <span class="right badge badge-info" v-if="nav.badge">{{ nav.badge }}</span>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" :style="{display: nav.active ? 'block' : 'none'}" v-for="child in nav.childList">
                                <li class="nav-item">
                                    <a :href="child.url" class="nav-link" :class="{'active':child.active}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ child.title }}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </template>
                </ul>
            </nav>
        </div>
    </aside>
</template>
