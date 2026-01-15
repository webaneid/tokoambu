# SOP: Autocomplete Component

## Overview

Autocomplete component adalah komponen JavaScript reusable yang menyediakan search & select functionality dengan UX yang konsisten di seluruh aplikasi Toko Ambu.

**File Location:** `/public/js/autocomplete.js`

## Features

- ✅ Real-time search/filter
- ✅ Customizable display template
- ✅ Multi-field search (name, SKU, etc.)
- ✅ Limited display (default: 10 items)
- ✅ Scrollable dropdown (max-height: 60)
- ✅ Auto-hide on outside click
- ✅ Callback on item selection
- ✅ Dynamic data update
- ✅ Reset functionality

## Basic Usage

### 1. HTML Structure

```html
<!-- Search Input (visible) -->
<div class="relative">
    <label class="block text-sm font-medium text-gray-700 mb-2">Label</label>

    <input
        type="text"
        id="search-input"
        autocomplete="off"
        placeholder="Ketik untuk mencari..."
        class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
    >

    <!-- Hidden input for form submission -->
    <input type="hidden" id="hidden-input" name="field_name" value="">

    <!-- Dropdown container -->
    <div id="dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
        <!-- Items will be populated here -->
    </div>
</div>
```

### 2. Include JavaScript

```html
<script src="{{ asset('js/autocomplete.js') }}"></script>
```

### 3. Initialize Autocomplete

```javascript
const myAutocomplete = new Autocomplete({
    inputId: 'search-input',           // ID of search input
    hiddenInputId: 'hidden-input',     // ID of hidden input for form
    dropdownId: 'dropdown',            // ID of dropdown container
    data: dataArray,                   // Array of objects to search
    searchFields: ['name', 'sku'],     // Fields to search in
    displayTemplate: (item) => {       // Custom display template
        return `<div class="font-medium">${item.name}</div>`;
    },
    maxItems: 10,                      // Max items to display
    onSelect: (item) => {              // Callback when item selected
        console.log('Selected:', item);
    }
});
```

## Complete Example: Product Autocomplete

### HTML

```html
<div class="relative">
    <label class="block text-sm font-medium text-gray-700 mb-2">Produk</label>

    <input
        type="text"
        id="product-search"
        autocomplete="off"
        placeholder="Ketik untuk mencari produk..."
        class="w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
    >

    <input type="hidden" id="product-id" name="product_id" value="">

    <div id="product-dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>

    @error('product_id')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
```

### JavaScript

```javascript
<script src="{{ asset('js/autocomplete.js') }}"></script>
<script>
    const products = @json($products);

    const productAutocomplete = new Autocomplete({
        inputId: 'product-search',
        hiddenInputId: 'product-id',
        dropdownId: 'product-dropdown',
        data: products,
        searchFields: ['name', 'sku'],
        displayTemplate: (product) => {
            let html = `<div class="font-medium">${product.name}</div>`;
            if (product.sku) {
                html += `<div class="text-xs text-gray-500">SKU: ${product.sku}</div>`;
            }
            return html;
        },
        maxItems: 10,
        onSelect: async (product) => {
            console.log('Product selected:', product);
            // Do something with selected product
            // e.g., load variants, update price, etc.
        }
    });
</script>
```

## Advanced Example: Variant Autocomplete (Conditional)

```javascript
let variantAutocomplete = null;

async function loadVariants(productId) {
    const response = await fetch(`/products/${productId}/variants`);
    const data = await response.json();

    // Map variants to autocomplete format
    const variantData = data.variants.map(variant => ({
        id: variant.id,
        name: Object.values(variant.variant_attributes).join(' / '),
        sku: variant.sku
    }));

    // Initialize or update variant autocomplete
    if (variantAutocomplete) {
        variantAutocomplete.updateData(variantData);
        variantAutocomplete.reset();
    } else {
        variantAutocomplete = new Autocomplete({
            inputId: 'variant-search',
            hiddenInputId: 'variant-id',
            dropdownId: 'variant-dropdown',
            data: variantData,
            searchFields: ['name', 'sku'],
            displayTemplate: (variant) => {
                return `
                    <div class="font-medium">${variant.name}</div>
                    <div class="text-xs text-gray-500">SKU: ${variant.sku}</div>
                `;
            },
            maxItems: 10,
            onSelect: (variant) => {
                console.log('Variant selected:', variant);
            }
        });
    }
}
```

## Configuration Options

| Option | Type | Required | Default | Description |
|--------|------|----------|---------|-------------|
| `inputId` | string | ✅ | - | ID of visible search input element |
| `hiddenInputId` | string | ✅ | - | ID of hidden input for form submission |
| `dropdownId` | string | ✅ | - | ID of dropdown container element |
| `data` | array | ✅ | `[]` | Array of objects to search through |
| `searchFields` | array | ❌ | `['name']` | Fields to search in each object |
| `displayTemplate` | function | ❌ | Default template | Function to render each item |
| `maxItems` | number | ❌ | `10` | Maximum items to display |
| `onSelect` | function | ❌ | Empty function | Callback when item is selected |

## Methods

### `updateData(newData)`
Update the data source dynamically.

```javascript
const newProducts = [{id: 1, name: 'Product 1'}, ...];
productAutocomplete.updateData(newProducts);
```

### `reset()`
Reset the autocomplete (clear inputs and hide dropdown).

```javascript
productAutocomplete.reset();
```

### `getSelected()`
Get the currently selected item.

```javascript
const selectedItem = productAutocomplete.getSelected();
console.log(selectedItem);
```

### `show()` / `hide()`
Manually show or hide the dropdown.

```javascript
productAutocomplete.show();
productAutocomplete.hide();
```

## Data Format

Data must be an array of objects with at least an `id` and searchable fields:

```javascript
const data = [
    {
        id: 1,
        name: 'Product Name',
        sku: 'SKU-001',
        // ... other fields
    },
    {
        id: 2,
        name: 'Another Product',
        sku: 'SKU-002',
        // ... other fields
    }
];
```

## Custom Display Template

The `displayTemplate` function receives the item object and must return HTML string:

```javascript
displayTemplate: (item) => {
    // Simple
    return `<div>${item.name}</div>`;

    // With multiple lines
    return `
        <div class="font-medium">${item.name}</div>
        <div class="text-xs text-gray-500">SKU: ${item.sku}</div>
        <div class="text-xs text-gray-400">Stock: ${item.stock}</div>
    `;

    // With conditional rendering
    let html = `<div class="font-medium">${item.name}</div>`;
    if (item.sku) {
        html += `<div class="text-xs text-gray-500">SKU: ${item.sku}</div>`;
    }
    if (item.stock > 0) {
        html += `<div class="text-xs text-green-600">In Stock: ${item.stock}</div>`;
    }
    return html;
}
```

## Styling Guidelines

### Required Classes

- **Input**: `w-full h-10 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-primary`
- **Dropdown**: `hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto`
- **Item** (auto-added): `autocomplete-item px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer`

### Color Scheme (Toko Ambu)

- Primary: `#F17B0D` (Orange) - untuk focus state
- Text: `text-gray-700` - untuk label
- Border: `border-gray-300` - untuk input border
- Hover: `hover:bg-gray-100` - untuk item hover

## Common Use Cases

### 1. Transfer Form (Product + Variant)
- Product autocomplete → Load variants → Variant autocomplete
- See: `/resources/views/warehouse/transfer/create.blade.php`

### 2. Purchase Form (Product + Variant)
- Product autocomplete → Load variants → Variant autocomplete → Show price
- See: `/resources/views/purchases/create.blade.php` (to be implemented)

### 3. Order Form (Customer + Product)
- Customer autocomplete
- Product autocomplete → Variant autocomplete
- (to be implemented)

### 4. Supplier/Customer Selection
- Simple autocomplete with name and contact info
- (to be implemented)

## Troubleshooting

### Dropdown tidak muncul
✅ Check: Apakah element IDs sudah benar?
✅ Check: Apakah data sudah di-pass?
✅ Check: Apakah z-index dropdown cukup tinggi?

### Item tidak bisa di-klik
✅ Check: Apakah dropdown punya class `cursor-pointer`?
✅ Check: Apakah ada element yang overlap?

### Search tidak work
✅ Check: Apakah `searchFields` array sudah sesuai dengan data object keys?
✅ Check: Apakah data object punya field yang di-search?

### Selected value tidak masuk form
✅ Check: Apakah hidden input punya `name` attribute?
✅ Check: Apakah hidden input ada di dalam `<form>` tag?

## Best Practices

1. **Always use hidden input** untuk form submission (jangan pakai visible input)
2. **Validate data** di backend, jangan rely on frontend saja
3. **Use meaningful IDs** untuk elements (e.g., `product-search`, bukan `input1`)
4. **Keep maxItems low** (10-15) untuk performance
5. **Use loading state** saat fetch API untuk variant
6. **Reset autocomplete** saat ganti context (e.g., ganti product → reset variant)
7. **Show feedback** ke user saat select (e.g., console.log atau update UI)

## Integration Checklist

Saat implement autocomplete di halaman baru:

- [ ] Copy HTML structure dengan IDs yang unique
- [ ] Include `autocomplete.js` script
- [ ] Pass data dari controller ke view
- [ ] Initialize Autocomplete class
- [ ] Implement `onSelect` callback
- [ ] Test search functionality
- [ ] Test form submission
- [ ] Test validation error handling
- [ ] Test responsive design (mobile)

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2025-01-11 | Initial release - Product & Variant autocomplete |

## Support

Jika ada issue atau improvement ideas, silakan update file ini atau hubungi development team.

---

**Last Updated:** 2025-01-11
**Maintainer:** Development Team
**File Path:** `/public/js/autocomplete.js`
