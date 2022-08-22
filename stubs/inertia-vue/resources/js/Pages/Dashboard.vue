<script setup>
import LcgLayout from 'admin-lte-vue/layout/LteLayout.vue';

defineProps({
    lte: Object,
});
</script>

<template>
    <lcg-layout :lte="lte">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        Dashboard!
                    </div>
                </div>
            </div>
        </div>
    </lcg-layout>
</template>
