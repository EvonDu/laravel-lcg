<script setup>
import LcgLayout from 'admin-lte-vue/layout/LteLayout.vue';

defineProps({
    lte: Object,
});
</script>

<template>
    <lcg-layout :lte="lte">
        <div class="card card-primary card-outline">
            <div class="card-body">
                <h5>Dashboard!</h5>
            </div>
        </div>
    </lcg-layout>
</template>
