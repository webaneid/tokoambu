/**
 * Reusable Autocomplete Component
 *
 * Usage:
 * new Autocomplete({
 *     inputId: 'search-input',
 *     hiddenInputId: 'hidden-input',
 *     dropdownId: 'dropdown',
 *     data: [{id: 1, name: 'Item 1', sku: 'SKU-001'}, ...],
 *     searchFields: ['name', 'sku'],
 *     displayTemplate: (item) => `<div>${item.name}</div>`,
 *     maxItems: 10,
 *     onSelect: (item) => { console.log('Selected:', item); }
 * });
 */

class Autocomplete {
    constructor(options) {
        this.searchInput = document.getElementById(options.inputId);
        this.hiddenInput = document.getElementById(options.hiddenInputId);
        this.dropdown = document.getElementById(options.dropdownId);
        this.data = options.data || [];
        this.searchFields = options.searchFields || ['name'];
        this.displayTemplate = options.displayTemplate || this.defaultTemplate.bind(this);
        this.maxItems = options.maxItems || 10;
        this.onSelect = options.onSelect || (() => {});
        this.selectedItem = null;

        this.init();
    }

    init() {
        // Search input event
        this.searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.trim();
            this.filter(searchTerm);
        });

        // Show dropdown on focus
        this.searchInput.addEventListener('focus', () => {
            const searchTerm = this.searchInput.value.trim();
            this.filter(searchTerm);
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) && !this.dropdown.contains(e.target)) {
                this.hide();
            }
        });
    }

    filter(searchTerm) {
        // Filter items based on search term
        const filtered = this.data.filter(item => {
            return this.searchFields.some(field => {
                const value = item[field];
                return value && value.toString().toLowerCase().includes(searchTerm.toLowerCase());
            });
        });

        // Limit to max items
        const limited = filtered.slice(0, this.maxItems);

        this.render(limited, searchTerm);
    }

    render(items, searchTerm) {
        if (items.length === 0) {
            this.dropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada data ditemukan</div>';
        } else {
            this.dropdown.innerHTML = items.map(item => {
                return `<div class="autocomplete-item px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer" data-item-id="${item.id}">${this.displayTemplate(item)}</div>`;
            }).join('');

            // Attach click handlers
            this.dropdown.querySelectorAll('.autocomplete-item').forEach(element => {
                element.addEventListener('click', () => {
                    const itemId = element.dataset.itemId;
                    const item = this.data.find(i => i.id == itemId);
                    this.select(item);
                });
            });
        }

        this.show();
    }

    select(item) {
        this.selectedItem = item;
        this.searchInput.value = item.name || item.id;
        this.hiddenInput.value = item.id;
        this.hide();
        this.searchInput.blur(); // Remove focus to prevent re-opening
        this.onSelect(item);
    }

    show() {
        this.dropdown.classList.remove('hidden');
    }

    hide() {
        this.dropdown.classList.add('hidden');
        this.dropdown.innerHTML = ''; // Clear dropdown content
    }

    defaultTemplate(item) {
        let html = `<div class="font-medium">${item.name}</div>`;
        if (item.sku) {
            html += `<div class="text-xs text-gray-500">SKU: ${item.sku}</div>`;
        }
        return html;
    }

    // Update data source
    updateData(newData) {
        this.data = newData;
    }

    // Reset autocomplete
    reset() {
        this.searchInput.value = '';
        this.hiddenInput.value = '';
        this.selectedItem = null;
        this.hide();
    }

    // Get selected item
    getSelected() {
        return this.selectedItem;
    }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Autocomplete;
}

// Ensure global access when loaded via script tag.
if (typeof window !== 'undefined') {
    window.Autocomplete = Autocomplete;
}
