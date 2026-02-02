
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing search and filter...');
    
    const searchInput = document.querySelector('.search-box input');
    const categoryFilter = document.querySelector('.filter-select');
    const sortFilter = document.querySelectorAll('.filter-select')[1];
    const productsGrid = document.getElementById('productsGrid');
    const productCards = document.querySelectorAll('.product-card');
    
    if (!searchInput || !categoryFilter || !sortFilter || !productsGrid || productCards.length === 0) {
        console.error('Required elements not found:', {
            searchInput: !!searchInput,
            categoryFilter: !!categoryFilter,
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
        const priceText = card.querySelector('.product-price')?.textContent || '0';
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
            
            const matchesSearch = productName.includes(searchTerm) || 
                                 productDesc.includes(searchTerm) || 
                                 productCategory.includes(searchTerm);
            
            card.style.display = matchesSearch ? 'block' : 'none';
            if (matchesSearch) hasMatches = true;
        });
        
        showNoProductsMessage(!hasMatches);
    }
    
    function filterByCategory() {
        const selectedCategory = categoryFilter.value;
        console.log('Filtering by category:', selectedCategory);
        
        let hasMatches = false;
        
        productCards.forEach(card => {
            if (selectedCategory === 'All Categories') {
                card.style.display = 'block';
                hasMatches = true;
            } else {
                const cardCategory = card.querySelector('.product-category')?.textContent || '';
                const matchesCategory = cardCategory.includes(selectedCategory);
                card.style.display = matchesCategory ? 'block' : 'none';
                if (matchesCategory) hasMatches = true;
            }
        });
        
        showNoProductsMessage(!hasMatches);
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
                
            default:
                visibleProducts.sort((a, b) => {
                    const indexA = parseInt(a.getAttribute('data-index'));
                    const indexB = parseInt(b.getAttribute('data-index'));
                    return indexA - indexB;
                });
        }
        
        visibleProducts.forEach(product => {
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
                    <p>Try adjusting your filters or search terms</p>
                `;
                productsGrid.appendChild(noProductsMsg);
            }
        } else if (noProductsMsg) {
            noProductsMsg.remove();
        }
    }
    
    searchInput.addEventListener('input', searchProducts);
    categoryFilter.addEventListener('change', filterByCategory);
    sortFilter.addEventListener('change', sortProducts);
    
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
    
    searchInput.addEventListener('input', debounce(searchProducts, 300));
    
    console.log('Search and filter initialized successfully');
    });
    
    function filterByBadge(badgeType) {
    const productCards = document.querySelectorAll('.product-card');
    let hasMatches = false;
    
    productCards.forEach(card => {
        const hasBadge = card.querySelector(`.${badgeType}`);
        card.style.display = hasBadge ? 'block' : 'none';
        if (hasBadge) hasMatches = true;
    });
    
    const productsGrid = document.getElementById('productsGrid');
    if (productsGrid) {
        let noProductsMsg = document.querySelector('.no-products');
        
        if (!hasMatches) {
            if (!noProductsMsg) {
                noProductsMsg = document.createElement('div');
                noProductsMsg.className = 'no-products';
                noProductsMsg.innerHTML = `
                    <i class="fas fa-box-open"></i>
                    <h3>No products with ${badgeType} badge found</h3>
                    <p>Try a different filter</p>
                `;
                productsGrid.appendChild(noProductsMsg);
            }
        } else if (noProductsMsg) {
            noProductsMsg.remove();
        }
    }
    }
    

    