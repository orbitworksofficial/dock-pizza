import './bootstrap';
import Alpine from 'alpinejs';

/**
 * Theme: resolved before paint by an inline script in the layout head, so
 * this only handles toggling and persistence.
 */
window.adminTheme = function () {
    return {
        theme: document.documentElement.dataset.theme || 'light',

        toggle() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = this.theme;
            try {
                localStorage.setItem('admin_theme', this.theme);
            } catch (e) {
                // Private mode — the choice just will not persist.
            }
        },
    };
};

/**
 * Toasts. Server-rendered flashes are pushed in by the layout; JS can add
 * more via window.toast(...).
 */
Alpine.store('toasts', {
    items: [],
    nextId: 1,

    push(message, type = 'ok', timeout = 4500) {
        const id = this.nextId++;
        this.items.push({ id, message, type });
        if (timeout) {
            setTimeout(() => this.dismiss(id), timeout);
        }
        return id;
    },

    dismiss(id) {
        const i = this.items.findIndex((t) => t.id === id);
        if (i !== -1) this.items.splice(i, 1);
    },
});

window.toast = (message, type = 'ok') => Alpine.store('toasts').push(message, type);

/**
 * Sidebar for narrow screens.
 */
Alpine.store('sidebar', {
    open: false,
    toggle() { this.open = !this.open; },
    close() { this.open = false; },
});

/**
 * Character counter shared by every SEO-style field.
 */
window.charCounter = function (initial = '', warn = 60, max = 70) {
    return {
        value: initial,
        get count() { return this.value.length; },
        get counterClass() {
            if (this.count > max) return 'is-over';
            if (this.count > warn) return 'is-warn';
            return '';
        },
    };
};

window.Alpine = Alpine;
Alpine.start();
