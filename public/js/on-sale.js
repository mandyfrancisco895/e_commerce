document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing search and filter...');
    
    const searchInput = document.querySelector('.search-box input');
    const sortFilter = document.querySelector('.filter-select'); // Only one filter now
    const productsGrid = document.getElementById('productsGrid');
    const productCards = document.querySelectorAll('.product-card');
    
    if (!searchInput || !sortFilter || !productsGrid || productCards.length === 0) {
        console.error('Required elements not found:', {
            searchInput: !!searchInput,
            sortFilter: !!sortFilter,
            productsGrid: !!productsGrid,
            productCards: productCards.length
        });
        return;
    }
    
    console.log('Found elements:', productCards.length, 'product cards');
    
    const originalProducts = Array.from(productCards);
    
    productCards.forEach((card, index) => {
        const productId = card.querySelector('.add-to-cart')?.getAttribute('data-product-id') || index;
        const priceText = card.querySelector('.current-price')?.textContent || 
                         card.querySelector('.product-price')?.textContent || '0';
        const price = parseFloat(priceText.replace('₱', '').replace(',', '')) || 0;
        
        card.setAttribute('data-id', productId);
        card.setAttribute('data-price', price);
        card.setAttribute('data-index', index);
    });
    
    function searchProducts() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        console.log('Searching for:', searchTerm);
        
        let hasMatches = false;
        
        productCards.forEach(card => {
            const productName = card.querySelector('.product-title')?.textContent.toLowerCase() || '';
            const productDesc = card.querySelector('.product-description')?.textContent.toLowerCase() || '';
            const productCategory = card.querySelector('.product-category')?.textContent.toLowerCase() || '';
            
            const matchesSearch = searchTerm === '' || 
                                 productName.includes(searchTerm) || 
                                 productDesc.includes(searchTerm) || 
                                 productCategory.includes(searchTerm);
            
            card.style.display = matchesSearch ? 'block' : 'none';
            if (matchesSearch) hasMatches = true;
        });
        
        showNoProductsMessage(!hasMatches);
        sortProducts(); // Re-sort after search
    }
    
    function sortProducts() {
        const sortOption = sortFilter.value;
        console.log('Sorting by:', sortOption);
        
        const visibleProducts = Array.from(productCards).filter(card => 
            card.style.display !== 'none'
        );
        
        if (visibleProducts.length === 0) return;
        
        switch(sortOption) {
            case 'Price: Low to High':
                visibleProducts.sort((a, b) => {
                    const priceA = parseFloat(a.getAttribute('data-price'));
                    const priceB = parseFloat(b.getAttribute('data-price'));
                    return priceA - priceB;
                });
                break;
                
            case 'Price: High to Low':
                visibleProducts.sort((a, b) => {
                    const priceA = parseFloat(a.getAttribute('data-price'));
                    const priceB = parseFloat(b.getAttribute('data-price'));
                    return priceB - priceA;
                });
                break;
                
            case 'Newest First':
                visibleProducts.sort((a, b) => {
                    const idA = parseInt(a.getAttribute('data-id'));
                    const idB = parseInt(b.getAttribute('data-id'));
                    return idB - idA;
                });
                break;
                
            case 'Most Popular':
                visibleProducts.sort((a, b) => {
                    const idA = parseInt(a.getAttribute('data-id'));
                    const idB = parseInt(b.getAttribute('data-id'));
                    return idB - idA;
                });
                break;
                
            default: // Default Sorting
                visibleProducts.sort((a, b) => {
                    const indexA = parseInt(a.getAttribute('data-index'));
                    const indexB = parseInt(b.getAttribute('data-index'));
                    return indexA - indexB;
                });
        }
        
        // Clear products grid and re-append sorted products
        const hiddenProducts = Array.from(productCards).filter(card => 
            card.style.display === 'none'
        );
        
        productsGrid.innerHTML = '';
        visibleProducts.forEach(product => {
            productsGrid.appendChild(product);
        });
        hiddenProducts.forEach(product => {
            productsGrid.appendChild(product);
        });
    }
    
    function showNoProductsMessage(show) {
        let noProductsMsg = document.querySelector('.no-products');
        
        if (show) {
            if (!noProductsMsg) {
                noProductsMsg = document.createElement('div');
                noProductsMsg.className = 'no-products';
                noProductsMsg.innerHTML = `
                    <i class="fas fa-box-open"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your search terms</p>
                `;
                productsGrid.appendChild(noProductsMsg);
            }
        } else if (noProductsMsg) {
            noProductsMsg.remove();
        }
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Event listeners
    searchInput.addEventListener('input', debounce(searchProducts, 300));
    sortFilter.addEventListener('change', sortProducts);
    
    console.log('Search and filter initialized successfully');
});