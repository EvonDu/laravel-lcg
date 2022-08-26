require('./bootstrap');

import 'admin-lte-vue/lte.js';
import 'admin-lte-vue/lte.scss';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';

import AdminLTE from 'admin-lte-vue/plugins/admin-lte';
import AdminLTETools from 'admin-lte-vue/plugins/tools';
import ElementPlus from 'element-plus';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => require(`./Pages/${name}`),
    setup({ el, app, props, plugin }) {
        return createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(AdminLTE)
            .use(AdminLTETools)
            .use(ElementPlus)
            .mixin({ methods: { route } })
            .mount(el);
    },
});

InertiaProgress.init({ color: '#4B5563' });
