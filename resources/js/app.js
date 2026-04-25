import { createApp, createSSRApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from 'ziggy-js'
import { createPinia } from 'pinia'

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        const isServer = typeof window === 'undefined'
        const ziggyConfig = props.initialPage.props.ziggy

        const vueApp = (isServer ? createSSRApp : createApp)({
            render: () => h(App, props),
        })

        const pinia = createPinia()

        vueApp
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue, {
                ...ziggyConfig,
                location: new URL(ziggyConfig.location),
        })

        if (!isServer && el) {
            vueApp.mount(el)
        }

        return vueApp
    },
})
