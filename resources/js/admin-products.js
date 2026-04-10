document.addEventListener('DOMContentLoaded', function() {
    // Sample product data (replace with real data from API)
    const products = [
        {
            id: 1,
            name: 'Produk Makanan Ringan Premium',
            description: 'Makanan ringan dengan bahan berkualitas tinggi',
            category: 'Food & Beverage',
            price: 50000,
            stock: 100,
            status: 'active',
            image: 'https://via.placeholder.com/150'
        },
        {
            id: 2,
            name: 'Software Manajemen Bisnis',
            description: 'Solusi lengkap untuk manajemen bisnis Anda',
            category: 'Software',
            price: 2500000,
            stock: 15,
            status: 'active',
            image: 'https://via.placeholder.com/150'
        },
        {
            id: 3,
            name: 'Smart Device IoT',
            description: 'Perangkat IoT untuk otomatisasi rumah',
            category: 'Hardware',
            price: 1500000,
            stock: 0,
            status: 'out',
            image: 'https://via.placeholder.com/150'
        },
        // Add more sample products...
    ];

    // DOM Elements
    const productsTableBody = document.getElementById('productsTableBody');
    const showingCount = document.getElementById('showingCount');
    const totalCount = document.getElementById('totalCount');
    const searchInput = document.querySelector('.search-input');
    const categoryFilter = document.querySelector('.category-filter');
    const statusFilter = document.querySelector('.status-filter');
    const addProductBtn = document.getElementById('addProductBtn');
    const productModal = document.getElementById('productModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');

    // Pagination variables
    let currentPage = 1;
    const itemsPerPage = 10;

    // Initialize the page
    function init() {
        renderProducts(products);
        setupEventListeners();
        updatePagination();
    }

    // Render products to the table
    function renderProducts(productsToRender) {
        productsTableBody.innerHTML = '';
        
        const startIndex = (currentPage - 1) * itemsPerPage;
        const paginatedProducts = productsToRender.slice(startIndex, startIndex + itemsPerPage);
        
        if (paginatedProducts.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                    Tidak ada produk yang ditemukan
                </td>
            `;
            productsTableBody.appendChild(emptyRow);
        } else {
            paginatedProducts.forEach(product => {
                const statusClass = {
                    'active': 'status-active',
                    'inactive': 'status-inactive',
                    'out': 'status-out'
                }[product.status] || '';
                
                const statusText = {
                    'active': 'Aktif',
                    'inactive': 'Nonaktif',
                    'out': 'Habis'
                }[product.status] || '';
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" class="product-checkbox rounded text-red-600 focus:ring-red-500">
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full" src="${product.image}" alt="${product.name}">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${product.name}</div>
                                <div class="text-sm text-gray-500">${product.description}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${product.category}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">Rp ${product.price.toLocaleString('id-ID')}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${product.stock}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button class="edit-product text-red-600 hover:text-red-900 mr-3" data-id="${product.id}">Edit</button>
                        <button class="delete-product text-gray-600 hover:text-gray-900" data-id="${product.id}">Hapus</button>
                    </td>
                `;
                productsTableBody.appendChild(row);
            });
        }
        
        showingCount.textContent = paginatedProducts.length;
        totalCount.textContent = productsToRender.length;
        updatePaginationControls(productsToRender.length);
    }

    // Filter products based on search and filters
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryValue = categoryFilter.value;
        const statusValue = statusFilter.value;
        
        const filteredProducts = products.filter(product => {
            const matchesSearch = product.name.toLowerCase().includes(searchTerm) || 
                               product.description.toLowerCase().includes(searchTerm);
            
            const matchesCategory = categoryValue === 'all' || 
                                  product.category.toLowerCase().includes(categoryValue);
            
            const matchesStatus = statusValue === 'all' || 
                                 product.status === statusValue;
            
            return matchesSearch && matchesCategory && matchesStatus;
        });
        
        currentPage = 1; // Reset to first page when filtering
        renderProducts(filteredProducts);
    }

    // Update pagination controls
    function updatePaginationControls(totalItems) {
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;
    }

    // Setup event listeners
    function setupEventListeners() {
        // Search functionality
        searchInput.addEventListener('keyup', debounce(filterProducts, 300));
        
        // Filter functionality
        categoryFilter.addEventListener('change', filterProducts);
        statusFilter.addEventListener('change', filterProducts);
        
        // Modal functionality
        addProductBtn.addEventListener('click', function() {
            productModal.classList.remove('hidden');
        });
        
        closeModalBtn.addEventListener('click', function() {
            productModal.classList.add('hidden');
        });
        
        // Close modal when clicking outside
        productModal.addEventListener('click', function(e) {
            if (e.target === productModal) {
                productModal.classList.add('hidden');
            }
        });
        
        // Select all checkbox
        document.querySelector('.select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Pagination controls
        prevPageBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                filterProducts();
            }
        });
        
        nextPageBtn.addEventListener('click', function() {
            const totalPages = Math.ceil(products.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                filterProducts();
            }
        });
    }

    // Debounce function for search input
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }

    // Initialize the page
    init();
});