<!-- Modal Tambah Kategori -->
<div id="modal_add_category" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
        <h4 class="text-lg font-semibold mb-4">Tambah Kategori Produk</h4>
        <form id="form_add_category">
            <div class="mb-4">
                <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" id="category_name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Nama kategori" required>
                <p id="error_category_name" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>
            <div class="mb-6">
                <label for="category_description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea id="category_description" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Deskripsi kategori"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" id="btn_close_modal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</button>
                <button type="submit" id="btn_submit_category" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Supplier -->
<div id="modal_add_supplier" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
        <h4 class="text-lg font-semibold mb-4">Tambah Supplier</h4>
        <form id="form_add_supplier">
            <div class="mb-4">
                <label for="supplier_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Supplier <span class="text-red-500">*</span></label>
                <input type="text" id="supplier_name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Nama supplier" required>
                <p id="error_supplier_name" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>
            <div class="mb-4">
                <label for="supplier_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="supplier_email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="email@example.com">
            </div>
            <div class="mb-4">
                <label for="supplier_phone" class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                <input type="text" id="supplier_phone" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="08xx xxxx xxxx">
            </div>
            <div class="mb-6">
                <label for="supplier_address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                <textarea id="supplier_address" name="address" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary" placeholder="Alamat lengkap"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" id="btn_close_supplier_modal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Batal</button>
                <button type="submit" id="btn_submit_supplier" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">Simpan Supplier</button>
            </div>
        </form>
    </div>
</div>
