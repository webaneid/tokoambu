/**
 * Footer Menu Manager
 * Handles CRUD operations and drag & drop for footer menu items
 */

document.addEventListener('DOMContentLoaded', function() {
    const menuList = document.getElementById('footer-menu-list');
    const btnAddMenu = document.getElementById('btnAddFooterMenu');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Initialize Sortable for drag & drop
    if (menuList && typeof Sortable !== 'undefined') {
        new Sortable(menuList, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                saveMenuOrder();
            }
        });
    }

    // Add Menu Button
    if (btnAddMenu) {
        btnAddMenu.addEventListener('click', function() {
            showMenuModal();
        });
    }

    // Edit Menu Buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit-menu')) {
            const btn = e.target.closest('.btn-edit-menu');
            const menuId = btn.dataset.id;
            showMenuModal(menuId);
        }
    });

    // Delete Menu Buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-delete-menu')) {
            const btn = e.target.closest('.btn-delete-menu');
            const menuId = btn.dataset.id;
            deleteMenu(menuId);
        }
    });

    // Toggle Active Status
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('toggle-active')) {
            const menuId = e.target.dataset.id;
            const isActive = e.target.checked;
            toggleMenuActive(menuId, isActive);
        }
    });

    /**
     * Show Add/Edit Menu Modal
     */
    function showMenuModal(menuId = null) {
        const isEdit = menuId !== null;
        const title = isEdit ? 'Edit Menu' : 'Tambah Menu Baru';

        // Create modal HTML
        const modalHTML = `
            <div id="menuModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">${title}</h3>
                            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeMenuModal()">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form id="menuForm" class="space-y-4">
                            <input type="hidden" name="menu_id" id="menu_id" value="${menuId || ''}">

                            <!-- Label -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Label Menu *</label>
                                <input type="text" name="label" id="menu_label" required class="w-full rounded-lg border-gray-300 px-3 py-2 focus:border-primary focus:ring-primary" placeholder="Tentang Kami">
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Link *</label>
                                <select name="type" id="menu_type" required class="w-full rounded-lg border-gray-300 px-3 py-2 focus:border-primary focus:ring-primary">
                                    <option value="page">Halaman</option>
                                    <option value="custom_url">Custom URL</option>
                                </select>
                            </div>

                            <!-- Page Selection (shown when type=page) -->
                            <div id="page_field">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Halaman *</label>
                                <select name="page_id" id="menu_page_id" class="w-full rounded-lg border-gray-300 px-3 py-2 focus:border-primary focus:ring-primary">
                                    <option value="">-- Pilih Halaman --</option>
                                    ${window.footerMenuPages.map(page => `<option value="${page.id}">${page.title}</option>`).join('')}
                                </select>
                            </div>

                            <!-- Custom URL (shown when type=custom_url) -->
                            <div id="url_field" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Custom URL *</label>
                                <input type="text" name="custom_url" id="menu_custom_url" class="w-full rounded-lg border-gray-300 px-3 py-2 focus:border-primary focus:ring-primary" placeholder="/shop">
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3 pt-4">
                                <button type="submit" class="flex-1 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover">
                                    ${isEdit ? 'Simpan Perubahan' : 'Tambah Menu'}
                                </button>
                                <button type="button" onclick="closeMenuModal()" class="rounded-lg bg-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-400">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Toggle fields based on type
        const typeSelect = document.getElementById('menu_type');
        const pageField = document.getElementById('page_field');
        const urlField = document.getElementById('url_field');
        const pageIdInput = document.getElementById('menu_page_id');
        const urlInput = document.getElementById('menu_custom_url');

        typeSelect.addEventListener('change', function() {
            if (this.value === 'page') {
                pageField.classList.remove('hidden');
                urlField.classList.add('hidden');
                pageIdInput.required = true;
                urlInput.required = false;
            } else {
                pageField.classList.add('hidden');
                urlField.classList.remove('hidden');
                pageIdInput.required = false;
                urlInput.required = true;
            }
        });

        // If editing, load menu data
        if (isEdit) {
            loadMenuData(menuId);
        }

        // Handle form submission
        document.getElementById('menuForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveMenu();
        });
    }

    /**
     * Close modal
     */
    window.closeMenuModal = function() {
        const modal = document.getElementById('menuModal');
        if (modal) {
            modal.remove();
        }
    };

    /**
     * Load menu data for editing
     */
    function loadMenuData(menuId) {
        const menuItem = document.querySelector(`.footer-menu-item[data-id="${menuId}"]`);
        if (!menuItem) return;

        // Extract data from DOM (or make AJAX call if needed)
        const label = menuItem.querySelector('.font-medium').textContent;
        const infoText = menuItem.querySelector('.text-xs').textContent;

        document.getElementById('menu_label').value = label;

        // Determine type from info text
        if (infoText.startsWith('Page:')) {
            document.getElementById('menu_type').value = 'page';
            document.getElementById('menu_type').dispatchEvent(new Event('change'));
            // Set page_id based on stored data
        } else {
            document.getElementById('menu_type').value = 'custom_url';
            document.getElementById('menu_type').dispatchEvent(new Event('change'));
            const url = infoText.replace('Custom URL: ', '');
            document.getElementById('menu_custom_url').value = url;
        }
    }

    /**
     * Save menu (create or update)
     */
    function saveMenu() {
        const formData = new FormData(document.getElementById('menuForm'));
        const menuId = formData.get('menu_id');
        const isEdit = menuId && menuId !== '';

        const data = {
            label: formData.get('label'),
            type: formData.get('type'),
            page_id: formData.get('page_id') || null,
            custom_url: formData.get('custom_url') || null,
        };

        const url = isEdit
            ? `/admin/settings/footer-menu/${menuId}`
            : '/admin/settings/footer-menu';

        const method = isEdit ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                closeMenuModal();
                location.reload(); // Reload to show updated menu
            } else {
                alert('Error: ' + (result.message || 'Gagal menyimpan menu'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan menu');
        });
    }

    /**
     * Delete menu
     */
    function deleteMenu(menuId) {
        if (!confirm('Apakah Anda yakin ingin menghapus menu ini?')) {
            return;
        }

        fetch(`/admin/settings/footer-menu/${menuId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                location.reload(); // Reload to remove menu
            } else {
                alert('Error: ' + (result.message || 'Gagal menghapus menu'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus menu');
        });
    }

    /**
     * Toggle menu active status
     */
    function toggleMenuActive(menuId, isActive) {
        fetch(`/admin/settings/footer-menu/${menuId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                is_active: isActive
            })
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                alert('Error: ' + (result.message || 'Gagal mengubah status menu'));
                // Revert checkbox
                event.target.checked = !isActive;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengubah status menu');
            // Revert checkbox
            event.target.checked = !isActive;
        });
    }

    /**
     * Save menu order after drag & drop
     */
    function saveMenuOrder() {
        const items = Array.from(menuList.querySelectorAll('.footer-menu-item'));
        const orderData = items.map((item, index) => ({
            id: parseInt(item.dataset.id),
            order: index
        }));

        fetch('/admin/settings/footer-menu/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                items: orderData
            })
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                alert('Error: ' + (result.message || 'Gagal menyimpan urutan menu'));
                location.reload(); // Reload to revert order
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan urutan menu');
            location.reload(); // Reload to revert order
        });
    }
});
