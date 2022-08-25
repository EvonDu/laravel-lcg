<script setup>
import LteHeader from "../components/LteHeader.vue";
import LteSidebar from "../components/LteSidebar.vue";
import LteFooter from "../components/LteFooter.vue";

const props = defineProps({
    lte: Object,
    user: Object,
    status: String,
    title: {
        type: String,
        default: "AdminLTE",
    },
    breadcrumbs: {
        type: Array,
        default() {
            return [
                {"url": "#", "title": "Home"},
            ]
        }
    }
});
</script>

<template>
    <div class="hold-transition sidebar-mini">
        <div id="app" class="wrapper" v-cloak>
            <!-- header -->
            <lte-header
                :links="lte.header.links"
                :messages="lte.header.messages"
                :notifications="lte.header.notifications">
            </lte-header>
            <!-- sidebar -->
            <lte-sidebar
                :project="lte.project"
                :logo="lte.logo"
                :navs="lte.navs"
                :user="user">
            </lte-sidebar>
            <!-- content -->
            <div class="content-wrapper">
                <lte-content-header :title="title" :breadcrumbs="breadcrumbs"></lte-content-header>
                <section class="content">
                    <slot></slot>
                </section>
            </div>
            <!-- footer -->
            <lte-footer
                :version="lte.version"
                :content="lte.footer.text">
            </lte-footer>
            <!-- control-sidebar -->
            <!-- <aside class="control-sidebar control-sidebar-dark"></aside> -->
        </div>
    </div>
</template>
