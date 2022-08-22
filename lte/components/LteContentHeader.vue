<template>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <template v-for="item in breadcrumbs">
                            <li class="breadcrumb-item"><a :href="item.url">{{ item.title }}</a></li>
                        </template>
                        <li class="breadcrumb-item active"> {{ title }} </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: "LteContentHeader",
    props: {
        title: {
            type: String,
            default() {
                return "AdminLTE"
            }
        },
        breadcrumbs: {
            type: Array,
            default() {
                return [
                    {"url": "#", "title": "Home"},
                ]
            }
        },
    },
}
</script>

<style scoped>

</style>
