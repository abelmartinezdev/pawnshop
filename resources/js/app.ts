import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import type { DefineComponent } from 'vue'
import { createApp, h } from 'vue'

// ✅ estilos
import '../css/app.scss'

// ✅ dark/light (hazlo antes de montar para evitar “flash”)
import { initializeTheme } from './composables/useAppearance'
initializeTheme()

// ✅ Toast (Vue 3)
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

import { ZiggyVue } from '../../vendor/tightenco/ziggy';
// ✅ PrimeVue DatePicker
import PrimeVue from 'primevue/config'
import DatePicker from 'primevue/datepicker'
import 'primeicons/primeicons.css' // recomendado por PrimeVue

// ✅ Vue Multiselect
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
    ),
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) })

    vueApp.use(plugin)
    vueApp.use(ZiggyVue)
    // PrimeVue en unstyled para estilizar con Tailwind
    vueApp.use(PrimeVue, { unstyled: true }) // guía Tailwind + PrimeVue :contentReference[oaicite:2]{index=2}
    vueApp.component('DatePicker', DatePicker) // docs DatePicker :contentReference[oaicite:3]{index=3}

    // Toast tipo “toastr”
    vueApp.use(Toast, {
      timeout: 3000,
      closeOnClick: true,
      pauseOnHover: true,
    })

    // Multiselect global (opcional)
    vueApp.component('Multiselect', Multiselect)

    vueApp.mount(el)
  },
  progress: { color: '#4B5563' },
})