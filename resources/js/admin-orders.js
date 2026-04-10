document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const orderModal = document.getElementById('orderModal');
    const closeModal = document.getElementById('closeModal');
    const filterStatus = document.getElementById('filterStatus');
    const filterDate = document.getElementById('filterDate');
    const customDateRange = document.getElementById('customDateRange');
    const exportBtn = document.getElementById('exportBtn');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.w-64');

    // Event Listeners
    closeModal.addEventListener('click', () => {
        orderModal.classList.add('hidden');
    });

    filterDate.addEventListener('change', (e) => {
        if (e.target.value === 'custom') {
            customDateRange.classList.remove('hidden');
        } else {
            customDateRange.classList.add('hidden');
        }
    });

    exportBtn.addEventListener('click', () => {
        // Implement export functionality
        console.log('Export data');
    });

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('hidden');
    });

    // Close modal when clicking outside
    orderModal.addEventListener('click', (e) => {
        if (e.target === orderModal) {
            orderModal.classList.add('hidden');
        }
    });

    // View order details
    document.querySelectorAll('.view-order, #ordersTableBody tr').forEach(element => {
        element.addEventListener('click', function(e) {
            if (e.target.tagName === 'I' || e.target.tagName === 'BUTTON') {
                return;
            }
            
            const orderId = this.getAttribute('data-id') || this.querySelector('.view-order').getAttribute('data-id');
            fetchOrderDetails(orderId);
        });
    });

    // Update order status
    document.getElementById('updateStatusBtn').addEventListener('click', function() {
        const orderId = document.getElementById('orderId').textContent;
        const newStatus = document.getElementById('statusSelect').value;
        
        // Implement status update via API
        console.log(`Updating order ${orderId} to status ${newStatus}`);
    });

    // Functions
    function fetchOrderDetails(orderId) {
        // In a real app, you would fetch this from your Laravel API
        fetch(`/api/orders/${orderId}`)
            .then(response => response.json())
            .then(data => {
                populateOrderModal(data);
                orderModal.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error fetching order details:', error);
            });
    }

    function populateOrderModal(order) {
        // Populate modal with order data
        document.getElementById('orderId').textContent = order.order_number;
        document.getElementById('orderDate').textContent = new Date(order.created_at).toLocaleDateString('id-ID');
        document.getElementById('customerName').textContent = order.customer.name;
        document.getElementById('customerEmail').textContent = order.customer.email;
        document.getElementById('customerPhone').textContent = order.customer.phone;
        document.getElementById('customerAddress').textContent = order.shipping_address;
        
        // Order info
        document.getElementById('orderStatus').textContent = order.status_text;
        document.getElementById('orderStatus').setAttribute('data-status', order.status);
        document.getElementById('paymentMethod').textContent = order.payment_method;
        document.getElementById('paymentStatus').textContent = order.payment_status_text;
        document.getElementById('paymentStatus').setAttribute('data-status', order.payment_status);
        document.getElementById('shippingMethod').textContent = order.shipping_method;
        
        // Order summary
        document.getElementById('orderSubtotal').textContent = `Rp ${order.subtotal.toLocaleString('id-ID')}`;
        document.getElementById('orderShipping').textContent = `Rp ${order.shipping_cost.toLocaleString('id-ID')}`;
        document.getElementById('orderDiscount').textContent = `Rp ${order.discount.toLocaleString('id-ID')}`;
        document.getElementById('orderTotal').textContent = `Rp ${order.total_amount.toLocaleString('id-ID')}`;
        
        // Order items
        const orderItemsBody = document.getElementById('orderItemsBody');
        orderItemsBody.innerHTML = '';
        
        order.items.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.name}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">Rp ${item.price.toLocaleString('id-ID')}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">${item.quantity}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">Rp ${(item.price * item.quantity).toLocaleString('id-ID')}</td>
            `;
            orderItemsBody.appendChild(row);
        });
        
        // Set current status in select
        document.getElementById('statusSelect').value = order.status;
    }
});