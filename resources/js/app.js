import './bootstrap';
import './qr-scan';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import collapse from '@alpinejs/collapse';

Alpine.plugin(intersect);
Alpine.plugin(collapse);

/**
 * Reusable create/edit modal for admin resource pages.
 * One instance drives both the "New" and per-row "Edit" flows, and re-opens
 * itself (repopulated from old input) after a server-side validation failure.
 *
 * config: {
 *   base:     '/admin/subjects'   // resource collection URL
 *   defaults: { name: '', ... }   // blank-form field values
 *   autoOpen: 'create' | 'edit' | null
 *   editRow:  {...} | null        // row to load when autoOpen === 'edit'
 *   reopen:   { id, old } | null  // set when validation failed
 * }
 */
Alpine.data('resourceModal', (config) => ({
    open: false,
    editingId: null,
    form: {},

    init() {
        this.form = { ...config.defaults };

        if (config.reopen) {
            this.editingId = config.reopen.id ?? null;
            this.form = { ...config.defaults, ...config.reopen.old };
            this.open = true;
        } else if (config.autoOpen === 'edit' && config.editRow) {
            this.openEdit(config.editRow);
        } else if (config.autoOpen === 'create') {
            this.openCreate();
        }
    },

    get isEdit() {
        return this.editingId !== null;
    },
    get action() {
        return this.isEdit ? `${config.base}/${this.editingId}` : config.base;
    },

    openCreate() {
        this.editingId = null;
        this.form = { ...config.defaults };
        this.open = true;
    },
    openEdit(row) {
        this.editingId = row.id;
        this.form = { ...config.defaults, ...row };
        this.open = true;
    },
    close() {
        this.open = false;
    },
}));

window.Alpine = Alpine;

Alpine.start();
