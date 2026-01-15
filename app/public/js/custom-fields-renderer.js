/**
 * CustomFieldsRenderer
 * Renders custom fields dynamically based on product category
 */
class CustomFieldsRenderer {
    constructor(options = {}) {
        this.container = document.getElementById(options.containerId || 'customFieldsInputContainer');
        this.hiddenInput = document.getElementById(options.hiddenInputId || 'customFieldValuesData');
        this.categoryFields = []; // Will be populated from category
        this.values = options.initialValues || {};
        this.onChange = options.onChange || (() => {});

        if (options.autoInit !== false) {
            this.init();
        }
    }

    init() {
        // Initial render if fields exist
        if (this.categoryFields.length > 0) {
            this.render();
        }
    }

    setFields(fields) {
        this.categoryFields = fields || [];
        this.render();
    }

    setValues(values) {
        this.values = values || {};
        this.render();
    }

    updateValue(fieldId, value) {
        this.values[fieldId] = value;
        this.syncToHiddenInput();
        this.onChange(this.values);
    }

    syncToHiddenInput() {
        if (this.hiddenInput) {
            this.hiddenInput.value = JSON.stringify(this.values);
        }
    }

    render() {
        if (!this.container) return;

        // Clear container
        this.container.innerHTML = '';

        if (this.categoryFields.length === 0) {
            this.container.innerHTML = `
                <div class="text-center py-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                    <p class="text-sm text-gray-500">Kategori ini tidak memiliki custom field.</p>
                </div>
            `;
            return;
        }

        // Render each field
        this.categoryFields.forEach((field, index) => {
            const fieldElement = this.renderField(field, index);
            this.container.appendChild(fieldElement);
        });

        // Sync to hidden input
        this.syncToHiddenInput();
    }

    renderField(field, index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-4';

        const label = document.createElement('label');
        label.className = 'block text-sm font-medium text-gray-700 mb-2';
        label.textContent = field.label;

        if (field.required) {
            const asterisk = document.createElement('span');
            asterisk.className = 'text-red-500';
            asterisk.textContent = ' *';
            label.appendChild(asterisk);
        }

        wrapper.appendChild(label);

        // Render input based on type
        let input;
        const currentValue = this.values[field.id] || '';

        switch (field.type) {
            case 'textarea':
                input = this.createTextarea(field, currentValue);
                break;
            case 'number':
                input = this.createNumber(field, currentValue);
                break;
            case 'date':
                input = this.createDate(field, currentValue);
                break;
            case 'select':
                input = this.createSelect(field, currentValue);
                break;
            case 'text':
            default:
                input = this.createText(field, currentValue);
                break;
        }

        wrapper.appendChild(input);

        return wrapper;
    }

    createText(field, value) {
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary';
        input.value = value;
        input.placeholder = `Masukkan ${field.label.toLowerCase()}`;

        if (field.required) {
            input.required = true;
        }

        input.addEventListener('input', (e) => {
            this.updateValue(field.id, e.target.value);
        });

        return input;
    }

    createTextarea(field, value) {
        const textarea = document.createElement('textarea');
        textarea.className = 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary';
        textarea.rows = 3;
        textarea.value = value;
        textarea.placeholder = `Masukkan ${field.label.toLowerCase()}`;

        if (field.required) {
            textarea.required = true;
        }

        textarea.addEventListener('input', (e) => {
            this.updateValue(field.id, e.target.value);
        });

        return textarea;
    }

    createNumber(field, value) {
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary';
        input.value = value;
        input.placeholder = `Masukkan ${field.label.toLowerCase()}`;

        if (field.required) {
            input.required = true;
        }

        input.addEventListener('input', (e) => {
            this.updateValue(field.id, e.target.value);
        });

        return input;
    }

    createDate(field, value) {
        const input = document.createElement('input');
        input.type = 'date';
        input.className = 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary';
        input.value = value;

        if (field.required) {
            input.required = true;
        }

        input.addEventListener('change', (e) => {
            this.updateValue(field.id, e.target.value);
        });

        return input;
    }

    createSelect(field, value) {
        const select = document.createElement('select');
        select.className = 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary';

        if (field.required) {
            select.required = true;
        }

        // Add placeholder option
        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = `Pilih ${field.label.toLowerCase()}`;
        select.appendChild(placeholderOption);

        // Add options
        if (field.options && Array.isArray(field.options)) {
            field.options.forEach(option => {
                if (option) { // Skip empty options
                    const optionElement = document.createElement('option');
                    optionElement.value = option;
                    optionElement.textContent = option;
                    if (option === value) {
                        optionElement.selected = true;
                    }
                    select.appendChild(optionElement);
                }
            });
        }

        select.addEventListener('change', (e) => {
            this.updateValue(field.id, e.target.value);
        });

        return select;
    }

    clear() {
        this.values = {};
        this.categoryFields = [];
        if (this.container) {
            this.container.innerHTML = '';
        }
        this.syncToHiddenInput();
    }
}
