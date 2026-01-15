<!-- Add/Edit User Modal -->
<div id="userModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 id="userModalTitle" class="text-lg font-semibold">Tambah User</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeUserModal()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="userForm">
            <input type="hidden" id="userId" name="user_id">

            <div class="space-y-4">
                <!-- Name -->
                <div>
                    <label for="userName" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" id="userName" name="name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <p id="errorName" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Email -->
                <div>
                    <label for="userEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="userEmail" name="email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <p id="errorEmail" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Role -->
                <div>
                    <label for="userRole" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select id="userRole" name="role" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Pilih Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <p id="errorRole" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Password -->
                <div>
                    <label for="userPassword" class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span id="passwordOptional" class="text-gray-400 hidden">(kosongkan jika tidak ingin mengubah)</span>
                    </label>
                    <input type="password" id="userPassword" name="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <p id="errorPassword" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="userPasswordConfirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" id="userPasswordConfirmation" name="password_confirmation"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <p id="errorPasswordConfirmation" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeUserModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary-hover">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const usersData = @json($users);
const rolesData = @json($roles);

function openUserModal(userId = null) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const title = document.getElementById('userModalTitle');
    const passwordField = document.getElementById('userPassword');
    const passwordOptional = document.getElementById('passwordOptional');

    // Reset form
    form.reset();
    clearErrors();

    if (userId) {
        // Edit mode
        const user = usersData.find(u => u.id === userId);
        if (user) {
            title.textContent = 'Edit User';
            document.getElementById('userId').value = user.id;
            document.getElementById('userName').value = user.name;
            document.getElementById('userEmail').value = user.email;
            document.getElementById('userRole').value = user.roles[0]?.name || '';
            passwordField.removeAttribute('required');
            passwordOptional.classList.remove('hidden');
        }
    } else {
        // Add mode
        title.textContent = 'Tambah User';
        document.getElementById('userId').value = '';
        passwordField.setAttribute('required', 'required');
        passwordOptional.classList.add('hidden');
    }

    modal.classList.remove('hidden');
}

function closeUserModal() {
    document.getElementById('userModal').classList.add('hidden');
    clearErrors();
}

function clearErrors() {
    const errorElements = document.querySelectorAll('[id^="error"]');
    errorElements.forEach(el => {
        el.textContent = '';
        el.classList.add('hidden');
    });
}

function showError(field, message) {
    const errorEl = document.getElementById('error' + field.charAt(0).toUpperCase() + field.slice(1));
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }
}

// Handle form submission
document.getElementById('userForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    clearErrors();

    const userId = document.getElementById('userId').value;
    const formData = new FormData(this);

    const url = userId
        ? `{{ route('settings.users.update', ':id') }}`.replace(':id', userId)
        : '{{ route('settings.users.store') }}';

    const method = userId ? 'PUT' : 'POST';

    const data = {};
    formData.forEach((value, key) => {
        if (key !== 'user_id') data[key] = value;
    });

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            closeUserModal();
            location.reload(); // Reload to show updated list
        } else {
            if (result.errors) {
                Object.keys(result.errors).forEach(field => {
                    showError(field, result.errors[field][0]);
                });
            } else {
                alert(result.message || 'Terjadi kesalahan');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    }
});

// Add User button
document.getElementById('btnAddUser')?.addEventListener('click', function() {
    openUserModal();
});

// Edit User buttons
document.querySelectorAll('.btn-edit-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const userId = parseInt(this.getAttribute('data-id'));
        openUserModal(userId);
    });
});

// Delete User buttons
document.querySelectorAll('.btn-delete-user').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) {
            return;
        }

        const userId = this.getAttribute('data-id');
        const url = `{{ route('settings.users.delete', ':id') }}`.replace(':id', userId);

        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                location.reload();
            } else {
                alert(result.message || 'Terjadi kesalahan');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus user');
        }
    });
});
</script>
