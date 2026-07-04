import './bootstrap';
import '../css/app.css';
import 'primeicons/primeicons.css'; 

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura'; 
import { ZiggyVue } from '../../vendor/tightenco/ziggy'; 
import Tooltip from 'primevue/tooltip';

// ==========================================
// KOREKSI: Tambahkan Import Komponen PrimeVue
// ==========================================
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // ==========================================
        // KOREKSI: Daftarkan Komponen Secara Global
        // ==========================================
        app.component('Button', Button);
        app.component('InputText', InputText);
        app.component('Dropdown', Dropdown);
        app.component('Dialog', Dialog);

        app.use(plugin)
            .directive('tooltip', Tooltip)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        prefix: 'p',
                        darkModeSelector: false,
                        cssLayer: false
                    }
                }
            })
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});