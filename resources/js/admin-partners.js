document.addEventListener('DOMContentLoaded', function() {
    // Sample data - in Laravel this would come from API/Controller
    const partnersData = [
        {
            id: 1,
            name: "Budi Santoso",
            email: "budi@example.com",
            phone: "081234567890",
            business: "Toko Budi Makmur",
            type: "supplier",
            level: 2,
            joinDate: "2023-05-15",
            status: "active",
            sales: 12500000,
            commission: 1250000,
            products: 8,
            address: "Jl. Merdeka No. 123, Jakarta",
            whatsapp: "081234567890",
            taxId: "123456789012345",
            industry: "Retail"
        },
        // Add more sample data as needed
    ];

    // DOM Elements
    const partnersTableBody = document.getElementById('partnersTableBody');
    const showingCount = document.getElementById('showingCount');
    const totalCount = document.getElementById('totalCount');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const addPartnerBtn = document.getElementById('addPartnerBtn');
    const partnerModal = document.getElementById('partnerModal');
    const closeModal = document.getElementById('closeModal');
    const addPartnerModal = document.getElementById('addPartnerModal');
    const closeAddModal = document.getElementById('closeAddModal');
    const cancelAddPartner = document.getElementById('cancelAddPartner');
    const partnerForm = document.getElementById('partnerForm');
    const statusSelect = document.getElementById('statusSelect');
    const updateStatusBtn = document.getElementById('updateStatusBtn');
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContent = document.getElementById('tabContent');

    // Pagination variables
    let currentPage = 1;
    const itemsPerPage = 10;

    // Initialize
    function init() {
        renderPartnersTable();
        setupEventListeners();
    }

    // Render partners table
    function renderPartnersTable() {
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const paginatedData = partnersData.slice(startIndex, endIndex);

        partnersTableBody.innerHTML = '';
        
        paginatedData.forEach(partner => {
            const row = document.createElement('tr');
            row.className = 'table-row';
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full" src="../assets/user-avatar.png" alt="">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${partner.name}</div>
                            <div class="text-sm text-gray-500">${partner.business}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">${partner.email}</div>
                    <div class="text-sm text-gray-500">${partner.phone}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-badge ${partner.type}">${partner.type.charAt(0).toUpperCase() + partner.type.slice(1)}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${formatDate(partner.joinDate)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="status-badge ${partner.status}">${getStatusText(partner.status)}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${formatCurrency(partner.commission)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-red-600 hover:text-red-900 view-partner" data-id="${partner.id}">Detail</button>
                </td>
            `;
            partnersTableBody.appendChild(row);
        });

        // Update counters
        showingCount.textContent = paginatedData.length;
        totalCount.textContent = partnersData.length;

        // Update pagination buttons
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = endIndex >= partnersData.length;
    }

    // Setup event listeners
    function setupEventListeners() {
        // Pagination
        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderPartnersTable();
            }
        });

        nextPageBtn.addEventListener('click', () => {
            if (currentPage * itemsPerPage < partnersData.length) {
                currentPage++;
                renderPartnersTable();
            }
        });

        // Add partner modal
        addPartnerBtn.addEventListener('click', () => {
            addPartnerModal.classList.remove('hidden');
        });

        closeAddModal.addEventListener('click', () => {
            addPartnerModal.classList.add('hidden');
        });

        cancelAddPartner.addEventListener('click', () => {
            addPartnerModal.classList.add('hidden');
        });

        // Partner detail modal
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('view-partner')) {
                const partnerId = parseInt(e.target.getAttribute('data-id'));
                showPartnerDetails(partnerId);
            }
        });

        closeModal.addEventListener('click', () => {
            partnerModal.classList.add('hidden');
        });

        // Form submission
        partnerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // In Laravel, this would be an AJAX call to your backend
            alert('Partner added successfully!');
            addPartnerModal.classList.add('hidden');
            partnerForm.reset();
        });

        // Update status
        updateStatusBtn.addEventListener('click', () => {
            const newStatus = statusSelect.value;
            // In Laravel, this would be an AJAX call to update the status
            alert(`Status updated to: ${newStatus}`);
        });

        // Tab switching
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                loadTabContent(button.getAttribute('data-tab'));
            });
        });
    }

    // Show partner details
    function showPartnerDetails(partnerId) {
        const partner = partnersData.find(p => p.id === partnerId);
        if (!partner) return;

        // Update modal content
        document.getElementById('partnerId').textContent = partner.id;
        document.getElementById('partnerName').textContent = partner.name;
        document.getElementById('partnerEmail').textContent = partner.email;
        document.getElementById('partnerEmail2').textContent = partner.email;
        document.getElementById('partnerPhone').textContent = partner.phone;
        document.getElementById('partnerPhone2').textContent = partner.phone;
        document.getElementById('partnerWhatsapp').textContent = partner.whatsapp;
        document.getElementById('partnerAddress').textContent = partner.address;
        document.getElementById('partnerBusiness').textContent = partner.business;
        document.getElementById('partnerTaxId').textContent = partner.taxId;
        document.getElementById('partnerIndustry').textContent = partner.industry;
        document.getElementById('partnerJoinDate').textContent = formatDate(partner.joinDate);
        document.getElementById('totalSales').textContent = formatCurrency(partner.sales);
        document.getElementById('totalCommission').textContent = formatCurrency(partner.commission);
        document.getElementById('totalProducts').textContent = partner.products;
        document.getElementById('partnerLevel').textContent = `Level ${partner.level}`;

        // Update status badges
        const statusBadge = document.getElementById('partnerStatus');
        statusBadge.className = 'status-badge ' + partner.status;
        statusBadge.textContent = getStatusText(partner.status);

        const typeBadge = document.getElementById('partnerType');
        typeBadge.className = 'status-badge ' + partner.type;
        typeBadge.textContent = partner.type.charAt(0).toUpperCase() + partner.type.slice(1);

        // Set progress bars (example values)
        document.getElementById('salesProgress').style.width = '65%';
        document.getElementById('commissionProgress').style.width = '45%';

        // Set current status in select
        statusSelect.value = partner.status;

        // Load default tab
        loadTabContent('products');

        // Show modal
        partnerModal.classList.remove('hidden');
    }

    // Load tab content
    function loadTabContent(tabName) {
        let content = '';
        
        switch(tabName) {
            case 'products':
                content = `
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded" src="../assets/product-placeholder.jpg" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Produk Sample 1</div>
                                                <div class="text-sm text-gray-500">Kategori: Elektronik</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">SKU-001</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp 250.000</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">45</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge active">Aktif</span>
                                    </td>
                                </tr>
                                <!-- More product rows would go here -->
                            </tbody>
                        </table>
                    </div>
                `;
                break;
            case 'sales':
                content = `<p class="text-gray-500">Data penjualan akan ditampilkan di sini.</p>`;
                break;
            case 'commission':
                content = `<p class="text-gray-500">Data komisi akan ditampilkan di sini.</p>`;
                break;
            case 'documents':
                content = `<p class="text-gray-500">Dokumen mitra akan ditampilkan di sini.</p>`;
                break;
        }

        tabContent.innerHTML = content;
    }

    // Helper functions
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
    }

    function getStatusText(status) {
        const statusMap = {
            'active': 'Aktif',
            'pending': 'Menunggu Verifikasi',
            'rejected': 'Ditolak',
            'suspended': 'Ditangguhkan'
        };
        return statusMap[status] || status;
    }

    // Initialize the app
    init();
});