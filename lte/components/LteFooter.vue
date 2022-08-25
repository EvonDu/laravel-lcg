<script setup>
const props = defineProps({
    version: {
        type: String,
        default() {
            return "1.0.0";
        }
    },
    content: {
        type: String,
        default() {
            return "Copyright ©2022 Laravel.";
        }
    },
});
</script>

<template>
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> {{ this.version }}
        </div>
        <strong>{{ this.content }}</strong>
    </footer>
</template>
