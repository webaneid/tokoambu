/**
 * CustomFieldsRepeater
 * Manages dynamic custom field definitions for product categories
 */
class CustomFieldsRepeater {
    constructor(options = {}) {
        this.container = document.getElementById(options.containerId || 'customFieldsContainer');
        this.fields = options.initialFields || [];
        this.onChange = options.onChange || (() => {});

        this.init();
    }

    init() {
        this.render();
        this.attachEvents();
    }

    attachEvents() {
        // Add field button
        const addBtn = document.getElementById('btnAddCustomField');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.addField());
        }
    }

    addField(fieldData = null) {
        const newField = fieldData || {
            id: Date.now(),
            label: '',
            type: 'text',
            required: false,
            options: []
        };

        this.fields.push(newField);
        this.render();
        this.onChange(this.fields);
    }

    removeField(fieldId) {
        this.fields = this.fields.filter(f => f.id !== fieldId);
        this.render();
        this.onChange(this.fields);
    }

    updateField(fieldId, key, value) {
        const field = this.fields.find(f => f.id === fieldId);
        if (field) {
            field[key] = value;

            // Re-render if type changed (to show/hide options)
            if (key === 'type') {
                this.render();
            }

            this.onChange(this.fields);
        }
    }

    addOption(fieldId) {
        const field = this.fields.find(f => f.id === fieldId);
        if (field) {
            if (!field.options) field.options = [];
            field.options.push('');
            this.render();
            this.onChange(this.fields);
        }
    }

    removeOption(fieldId, optionIndex) {
        const field = this.fields.find(f => f.id === fieldId);
        if (field && field.options) {
            field.options.splice(optionIndex, 1);
            this.render();
            this.onChange(this.fields);
        }
    }

    updateOption(fieldId, optionIndex, value) {
        const field = this.fields.find(f => f.id === fieldId);
        if (field && field.options) {
            field.options[optionIndex] = value;
            this.onChange(this.fields);
        }
    }

    render() {
        if (!this.container) return;

        if (this.fields.length === 0) {
            this.container.innerHTML = `
                <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                    <p class="text-sm text-gray-500">Belum ada custom field.</p>
                    <p class="text-xs text-gray-400 mt-1">Tambahkan field untuk informasi produk yang lebih detail.</p>
                </div>
            `;
        } else {
            this.container.innerHTML = this.fields.map((field, index) => this.renderField(field, index)).join('');
        }

        // Re-attach event listeners after render
        this.attachFieldEvents();
    }

    renderField(field, index) {
        const typeOptions = [
            { value: 'text', label: 'Text' },
            { value: 'textarea', label: 'Textarea' },
            { value: 'number', label: 'Number' },
            { value: 'date', label: 'Date' },
            { value: 'select', label: 'Select' }
        ];

        return `
            <div class="bg-white border-2 border-gray-200 rounded-lg p-4 mb-3" data-field-id="${field.id}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold">${index + 1}</span>
                        <h4 class="text-sm font-semibold text-gray-700">Custom Field</h4>
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-700 text-sm font-medium" data-remove-field="${field.id}">
                        Hapus
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Label -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Label Field <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                            placeholder="Contoh: Penerbit, Tahun Terbit"
                            value="${field.label || ''}"
                            data-update-field="${field.id}"
                            data-update-key="label"
                        >
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Field <span class="text-red-500">*</span></label>
                        <select
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                            data-update-field="${field.id}"
                            data-update-key="type"
                        >
                            ${typeOptions.map(opt => `
                                <option value="${opt.value}" ${field.type === opt.value ? 'selected' : ''}>${opt.label}</option>
                            `).join('')}
                        </select>
                    </div>
                </div>

                <!-- Required Checkbox -->
                <div class="mt-3">
                    <label class="inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                            ${field.required ? 'checked' : ''}
                            data-update-field="${field.id}"
                            data-update-key="required"
                        >
                        <span class="ml-2 text-sm text-gray-700">Field wajib diisi</span>
                    </label>
                </div>

                <!-- Options (only for select type) -->
                ${field.type === 'select' ? this.renderOptions(field) : ''}
            </div>
        `;
    }

    renderOptions(field) {
        const options = field.options || [];

        return `
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-medium text-gray-700">Pilihan (Options)</label>
                    <button
                        type="button"
                        class="text-xs text-primary hover:text-primary-hover font-medium"
                        data-add-option="${field.id}"
                    >
                        + Tambah Pilihan
                    </button>
                </div>
                <div class="space-y-2" data-options-container="${field.id}">
                    ${options.length === 0 ? `
                        <p class="text-xs text-gray-400 italic">Belum ada pilihan. Klik "Tambah Pilihan" untuk menambahkan.</p>
                    ` : options.map((opt, idx) => `
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                                placeholder="Contoh: Erlangga, Gramedia"
                                value="${opt || ''}"
                                data-update-option="${field.id}"
                                data-option-index="${idx}"
                            >
                            <button
                                type="button"
                                class="text-red-600 hover:text-red-700 text-xs"
                                data-remove-option="${field.id}"
                                data-option-index="${idx}"
                            >
                                Hapus
                            </button>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    attachFieldEvents() {
        // Remove field
        document.querySelectorAll('[data-remove-field]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const fieldId = parseInt(e.target.dataset.removeField);
                this.removeField(fieldId);
            });
        });

        // Update field (text inputs and selects)
        document.querySelectorAll('[data-update-field]').forEach(input => {
            const fieldId = parseInt(input.dataset.updateField);
            const key = input.dataset.updateKey;

            if (input.type === 'checkbox') {
                input.addEventListener('change', (e) => {
                    this.updateField(fieldId, key, e.target.checked);
                });
            } else {
                input.addEventListener('input', (e) => {
                    this.updateField(fieldId, key, e.target.value);
                });
            }
        });

        // Add option
        document.querySelectorAll('[data-add-option]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const fieldId = parseInt(e.target.dataset.addOption);
                this.addOption(fieldId);
            });
        });

        // Remove option
        document.querySelectorAll('[data-remove-option]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const fieldId = parseInt(e.target.dataset.removeOption);
                const optionIndex = parseInt(e.target.dataset.optionIndex);
                this.removeOption(fieldId, optionIndex);
            });
        });

        // Update option
        document.querySelectorAll('[data-update-option]').forEach(input => {
            input.addEventListener('input', (e) => {
                const fieldId = parseInt(e.target.dataset.updateOption);
                const optionIndex = parseInt(e.target.dataset.optionIndex);
                this.updateOption(fieldId, optionIndex, e.target.value);
            });
        });
    }
}
