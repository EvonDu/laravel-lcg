<script setup>
import "bootstrap"
import defaultImage from '../images/user-128x128.jpg'
import { useForm } from "@inertiajs/inertia-vue3";

const props = defineProps({
    links: {
        type: Array,
        default() {
            return [
                //{ title: "Home",  url:'/' },
            ]
        }
    },
    messages: {
        type: Array,
        default() {
            return [
                //{ url: "#", img: "images/user-128x128.jpg", title: "Welcome", text: "welcome", time: "5 Hours Ago" },
            ]
        }
    },
    notifications: {
        type: Array,
        default() {
            return [
                //{url: "#", icon: "fas fa-users", text: "Welcome", time: "5 Hours Ago"},
            ]
        }
    }
});

const form = useForm({});

const logout = () => {
    form.post(route('logout'), {});
};
</script>

<template>
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block" v-for="link in links">
                <a :href="link.url" class="nav-link">{{ link.title }}</a>
            </li>
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Navbar Search -->
            <li class="nav-item">
                <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                    <i class="fas fa-search"></i>
                </a>
                <div class="navbar-search-block">
                    <form class="form-inline">
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-navbar" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </li>
            <!-- Function Item -->
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
            <!-- Messages Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="far fa-comments"></i>
                    <span class="badge badge-danger navbar-badge" v-if="messages.length">{{ messages.length }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <template v-for="message in messages">
                        <a :href="message.url" class="dropdown-item">
                            <!-- Message Start -->
                            <div class="media">
                                <img :src="messages.img" alt="User Avatar" class="img-size-50 img-circle mr-3" v-if="messages.img">
                                <img :src="defaultImage" alt="User Avatar" class="img-size-50 img-circle mr-3" v-else>
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        {{ message.title }}
                                        <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                                    </h3>
                                    <p class="text-sm">{{ message.text }}</p>
                                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> {{ message.time }}</p>
                                </div>
                            </div>
                            <!-- Message End -->
                        </a>
                        <div class="dropdown-divider"></div>
                    </template>
                    <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                </div>
            </li>
            <!-- Notifications Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge" v-if="notifications.length">{{ notifications.length }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ notifications.length }} Notifications</span>
                    <div class="dropdown-divider"></div>
                    <template v-for="notification in notifications">
                        <a :href="notification.url" class="dropdown-item">
                            <i class="mr-2" :class="notification.icon"></i> {{ notification.text }}
                            <span class="float-right text-muted text-sm">{{ notification.time }}</span>
                        </a>
                        <div class="dropdown-divider"></div>
                    </template>
                    <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                </div>
            </li>
            <!-- User Dropdown Menu -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="far fa-user-circle"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <!--<div class="dropdown-divider"></div>-->
                    <a @click="logout" class="dropdown-item dropdown-footer"> Log Out </a>
                </div>
            </li>
        </ul>
    </nav>
</template>

<style scoped>
/** 防止CSS警告 */
.dropdown-menu{
    margin: 0;
    padding: 0;
}
</style>
