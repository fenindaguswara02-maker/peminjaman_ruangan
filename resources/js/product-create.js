function calculateProfit() {
        const originalPrice = parseFloat(document.getElementById('original_price').value) || 0;
        const sellingPrice = parseFloat(document.getElementById('selling_price').value) || 0;
        const profit = sellingPrice - originalPrice;
        
        const profitDisplay = document.getElementById('profit_display');
        profitDisplay.textContent = formatRupiah(profit);
        
        if (profit >= 0) {
            profitDisplay.className = 'price-display profit';
        } else {
            profitDisplay.className = 'price-display loss';
        }
    }
    
    function formatRupiah(value) {
        return 'Rp ' + Math.abs(value).toLocaleString('id-ID');
    }
    
    // Preview gambar
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const previewContainer = document.querySelector('.border-dashed');
                previewContainer.innerHTML = `
                    <img src="${event.target.result}" class="mx-auto max-h-48 rounded-lg">
                    <p class="mt-2 text-sm text-gray-600">${file.name}</p>
                `;
            };
            reader.readAsDataURL(file);
        }
    });