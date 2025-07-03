import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import 'bootstrap';
import '../css/dashboard.css';
import '../css/registerForm.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

// PrimeVue imports
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';

// Only import PrimeIcons CSS
import 'primeicons/primeicons.css';

  // Theme
             // Core CSS


// PrimeVue Components (register globally)
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';
import ProgressSpinner from 'primevue/progressspinner';
import Tooltip from 'primevue/tooltip';
import '../css/dashboard.css';
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        prefix: 'p',
                        darkModeSelector: '.dark-mode',
                        cssLayer: false
                    }
                }
            });

        // Register PrimeVue components globally
        app.component('DataTable', DataTable);
        app.component('Column', Column);
        app.component('Button', Button);
        app.component('InputText', InputText);
        app.component('Dropdown', Dropdown);
        app.component('Calendar', Calendar);
        app.component('Tag', Tag);
        app.component('Avatar', Avatar);
        app.component('ProgressSpinner', ProgressSpinner);

        // Register directives
        app.directive('tooltip', Tooltip);

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
