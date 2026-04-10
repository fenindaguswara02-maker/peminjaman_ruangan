document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const customerModal = document.getElementById('customerModal');
    const editCustomerModal = document.getElementById('editCustomerModal');
    const closeModal = document.getElementById('closeModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const cancelEditCustomer = document.getElementById('cancelEditCustomer');
    const customerEditForm = document.getElementById('customerEditForm');
    const filterGender = document.getElementById('filterGender');
    const resetFilters = document.getElementById('resetFilters');
    
    // Close modals
    closeModal.addEventListener('click', () => customerModal.classList.add('hidden'));
    closeEditModal.addEventListener('click', () => editCustomerModal.classList.add('hidden'));
    cancelEditCustomer.addEventListener('click', () => editCustomerModal.classList.add('hidden'));
    
    // Filter handling
    filterGender.addEventListener('change', function() {
        const gender = this.value;
        const url = new URL(window.location.href);
        
        if (gender) {
            url.searchParams.set('gender', gender);
        } else {
            url.searchParams.delete('gender');
        }
        
        window.location.href = url.toString();
    });
    
    resetFilters.addEventListener('click', function() {
        const url = new URL(window.location.href);
        url.search = '';
        window.location.href = url.toString();
    });
    
    // Form submission
    customerEditForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const userId = document.getElementById('editCustomerId').value;
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="loading-spinner"></span> Memproses...';
        
        fetch(`/admin/customers/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                editCustomerModal.classList.add('hidden');
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', error.message || 'Terjadi kesalahan saat memperbarui data');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });
});

// Show customer details
window.showCustomerDetail = function(userId) {
    const modal = document.getElementById('customerModal');
    const loadingIndicator = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
    
    // Show loading state
    modal.querySelector('#tabContent').innerHTML = loadingIndicator;
    modal.classList.remove('hidden');
    
    fetch(`/admin/customers/${userId}`, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Gagal memuat data pelanggan');
        }
        return response.json();
    })
    .then(data => {
        // Populate modal
        document.getElementById('customerId').textContent = data.id;
        document.getElementById('customerName').textContent = data.name;
        document.getElementById('customerEmail').textContent = data.email;
        document.getElementById('customerEmail2').textContent = data.email;
        document.getElementById('customerPhone').textContent = data.phone || '-';
        document.getElementById('customerPhone2').textContent = data.phone || '-';
        document.getElementById('customerWhatsapp').textContent = data.phone || '-';
        document.getElementById('customerAvatar').src = data.avatar;
        document.getElementById('customerAddress').innerHTML = data.address ? data.address.replace(/\n/g, '<br>') : '-';
        
        // Gender badge
        const genderBadge = document.getElementById('customerGender');
        if (data.gender) {
            genderBadge.textContent = data.gender.charAt(0).toUpperCase() + data.gender.slice(1);
            genderBadge.className = 'gender-badge gender-' + data.gender;
            genderBadge.style.display = 'inline-block';
        } else {
            genderBadge.style.display = 'none';
        }
        
        // Stats
        document.getElementById('totalOrders').textContent = data.total_orders;
        document.getElementById('totalSpent').textContent = 'Rp ' + (data.total_spent ? data.total_spent.toLocaleString('id-ID') : '0');
        document.getElementById('lastOrder').textContent = data.last_order;
        document.getElementById('joinDate').textContent = data.join_date;
        
        // Progress bars
        document.getElementById('ordersProgress').style.width = Math.min(data.total_orders * 20, 100) + '%';
        document.getElementById('spendingProgress').style.width = Math.min(data.total_spent / 500000 * 100, 100) + '%';
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', error.message);
        customerModal.classList.add('hidden');
    });
};

// Edit customer
window.editCustomer = function(userId) {
    const modal = document.getElementById('editCustomerModal');
    
    fetch(`/admin/customers/${userId}/edit`, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Gagal memuat data pelanggan');
        }
        return response.json();
    })
    .then(data => {
        // Populate form
        document.getElementById('editCustomerId').value = data.id;
        document.getElementById('editName').value = data.name;
        document.getElementById('editEmail').value = data.email;
        document.getElementById('editPhone').value = data.phone || '';
        document.getElementById('editAddress').value = data.address || '';
        document.getElementById('editGender').value = data.gender || 'laki-laki';
        
        // Show modal
        modal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', error.message);
    });
};

// Toast notification
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}