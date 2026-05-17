import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('appShell', () => ({
        sidebarOpen: false,

        closeSidebar() {
            this.sidebarOpen = false;
        },
    }));
});

Alpine.start();
