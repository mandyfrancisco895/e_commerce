// Enhanced Search and Filter functionality for Sidebar
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing sidebar filters...');
    
    // Get DOM elements
    const searchInput = document.querySelector('.search-box input');
    const categoryRadios = document.querySelectorAll('input[name="category"]');
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');
    const applyPriceBtn = document.querySelector('.apply-price-btn');
    const sortSelect = document.getElementById('sortBy');
    const inStockCheckbox = document.getElementById('in-stock');
    const onSaleCheckbox = document.getElementById('on-sale');
    const newArrivalCheckbox = document.getElementById('new-arrival');
    const clearFiltersBtn = document.querySelector('.clear-filters-btn');
    const productsGrid = document.getElementById('productsGrid');
    const productCards = document.querySelectorAll('.product-card');
    const resultsCount = document.querySelector('.results-count');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    // Mobile sidebar toggle
    const mobileFilterToggle = document.getElementById('mobileFilterToggle');
    const closeSidebar = document.getElementById('closeSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const shopSidebar = document.getElementById('shopSidebar');
    
    // Store original products for resetting
    const originalProducts = Array.from(productCards);
    let currentProducts = Array.from(productCards);
    
    // Add data attributes to product cards for filtering
    productCards.forEach((card, index) => {
        const productId = card.querySelector('.add-to-cart')?.getAttribute('data-product-id') || index;
        const priceText = card.querySelector('.product-price')?.textContent || '0';
        const price = parseFloat(priceText.replace('₱', '').replace(',', '')) || 0;
        const stockText = card.querySelector('.product-stock')?.textContent || '0';
        const stock = parseInt(stockText.replace(/\D/g, '')) || 0;
        const hasSaleBadge = card.querySelector('.sale-badge') !== null;
        const hasNewBadge = card.querySelector('.new-arrivals-badge') !== null;
        
        card.setAttribute('data-id', productId);
        card.setAttribute('data-price', price);
        card.setAttribute('data-stock', stock);
        card.setAttribute('data-has-sale', hasSaleBadge);
        card.setAttribute('data-has-new', hasNewBadge);
        card.setAttribute('data-index', index);
    });
    
    // Initialize view toggle
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            viewButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const view = this.getAttribute('data-view');
            if (view === 'list') {
                productsGrid.classList.add('list-view');
            } else {
                productsGrid.classList.remove('list-view');
            }
        });
    });
    
    // Mobile sidebar functionality
    if (mobileFilterToggle && shopSidebar && sidebarOverlay && closeSidebar) {
        mobileFilterToggle.addEventListener('click', function() {
            shopSidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        closeSidebar.addEventListener('click', function() {
            shopSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        sidebarOverlay.addEventListener('click', function() {
            shopSidebar.classList.remove('active');
            this.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Search function
    function searchProducts() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        productCards.forEach(card => {
            const productName = card.querySelector('.product-title')?.textContent.toLowerCase() || '';
            const productDesc = card.querySelector('.product-description')?.textContent.toLowerCase() || '';
            const productCategory = card.querySelector('.product-category')?.textContent.toLowerCase() || '';
            
            const matchesSearch = productName.includes(searchTerm) || 
                                 productDesc.includes(searchTerm) || 
                                 productCategory.includes(searchTerm);
            
            if (matchesSearch) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
        
        updateResultsCount();
        applyAllFilters();
    }
    
    // Category filter
    function filterByCategory() {
        const selectedCategory = document.querySelector('input[name="category"]:checked').value;
        
        productCards.forEach(card => {
            if (selectedCategory === 'all') {
                card.style.display = '';
            } else {
                const cardCategory = card.getAttribute('data-category');
                card.style.display = cardCategory === selectedCategory ? '' : 'none';
            }
        });
        
        updateResultsCount();
        applyAllFilters();
    }
    
    // Price filter
    function filterByPrice() {
        const minPrice = parseFloat(minPriceInput.value) || 0;
        const maxPrice = parseFloat(maxPriceInput.value) || Infinity;
        
        productCards.forEach(card => {
            const price = parseFloat(card.getAttribute('data-price'));
            const isVisible = card.style.display !== 'none';
            
            if (isVisible && (price < minPrice || price > maxPrice)) {
                card.style.display = 'none';
            }
        });
        
        updateResultsCount();
    }
    
    // Quick filters
    function applyQuickFilters() {
        const inStockOnly = inStockCheckbox.checked;
        const onSaleOnly = onSaleCheckbox.checked;
        const newArrivalOnly = newArrivalCheckbox.checked;
        
        productCards.forEach(card => {
            const isVisible = card.style.display !== 'none';
            if (!isVisible) return;
            
            const stock = parseInt(card.getAttribute('data-stock'));
            const hasSale = card.getAttribute('data-has-sale') === 'true';
            const hasNew = card.getAttribute('data-has-new') === 'true';
            
            if ((inStockOnly && stock <= 0) ||
                (onSaleOnly && !hasSale) ||
                (newArrivalOnly && !hasNew)) {
                card.style.display = 'none';
            }
        });
        
        updateResultsCount();
    }
    
    // Sort products
    function sortProducts() {
        const sortOption = sortSelect.value;
        const visibleProducts = Array.from(productCards).filter(card => 
            card.style.display !== 'none'
        );
        
        if (visibleProducts.length === 0) return;
        
        switch(sortOption) {
            case 'price-low':
                visibleProducts.sort((a, b) => {
                    const priceA = parseFloat(a.getAttribute('data-price'));
                    const priceB = parseFloat(b.getAttribute('data-price'));
                    return priceA - priceB;
                });
                break;
                
            case 'price-high':
                visibleProducts.sort((a, b) => {
                    const priceA = parseFloat(a.getAttribute('data-price'));
                    const priceB = parseFloat(b.getAttribute('data-price'));
                    return priceB - priceA;
                });
                break;
                
            case 'newest':
                visibleProducts.sort((a, b) => {
                    const idA = parseInt(a.getAttribute('data-id'));
                    const idB = parseInt(b.getAttribute('data-id'));
                    return idB - idA;
                });
                break;
                
            case 'popular':
                // Placeholder - you might want to implement actual popularity sorting
                visibleProducts.sort((a, b) => {
                    const idA = parseInt(a.getAttribute('data-id'));
                    const idB = parseInt(b.getAttribute('data-id'));
                    return idB - idA;
                });
                break;
                
            case 'name-az':
                visibleProducts.sort((a, b) => {
                    const nameA = a.querySelector('.product-title')?.textContent || '';
                    const nameB = b.querySelector('.product-title')?.textContent || '';
                    return nameA.localeCompare(nameB);
                });
                break;
                
            case 'name-za':
                visibleProducts.sort((a, b) => {
                    const nameA = a.querySelector('.product-title')?.textContent || '';
                    const nameB = b.querySelector('.product-title')?.textContent || '';
                    return nameB.localeCompare(nameA);
                });
                break;
                
            default:
                visibleProducts.sort((a, b) => {
                    const indexA = parseInt(a.getAttribute('data-index'));
                    const indexB = parseInt(b.getAttribute('data-index'));
                    return indexA - indexB;
                });
        }
        
        // Reorder products in the grid
        productsGrid.innerHTML = '';
        visibleProducts.forEach(product => {
            productsGrid.appendChild(product);
            product.style.display = ''; // Ensure they're visible
        });
    }
    
    // Apply all filters
    function applyAllFilters() {
        const visibleBefore = Array.from(productCards).filter(card => 
            card.style.display !== 'none'
        ).length;
        
        // Apply price filter
        filterByPrice();
        
        // Apply quick filters
        applyQuickFilters();
        
        // Sort products
        sortProducts();
        
        updateResultsCount();
    }
    
    // Update results count
    function updateResultsCount() {
        const visibleCount = Array.from(productCards).filter(card => 
            card.style.display !== 'none'
        ).length;
        
        if (resultsCount) {
            resultsCount.textContent = `Showing ${visibleCount} of ${productCards.length} products`;
        }
        
        // Show no products message if needed
        showNoProductsMessage(visibleCount === 0);
    }
    
    // Show no products message
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
    
    // Clear all filters
    function clearAllFilters() {
        // Reset search
        searchInput.value = '';
        
        // Reset category
        document.getElementById('all-categories').checked = true;
        
        // Reset price
        minPriceInput.value = '';
        maxPriceInput.value = '';
        
        // Reset quick filters
        inStockCheckbox.checked = false;
        onSaleCheckbox.checked = false;
        newArrivalCheckbox.checked = false;
        
        // Reset sort
        sortSelect.value = 'default';
        
        // Show all products
        productCards.forEach(card => {
            card.style.display = '';
        });
        
        // Reset to original order
        productsGrid.innerHTML = '';
        originalProducts.forEach(product => {
            productsGrid.appendChild(product);
        });
        
        updateResultsCount();
    }
    
    // Event listeners
    searchInput.addEventListener('input', debounce(searchProducts, 300));
    
    categoryRadios.forEach(radio => {
        radio.addEventListener('change', filterByCategory);
    });
    
    applyPriceBtn.addEventListener('click', function() {
        applyAllFilters();
    });
    
    minPriceInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') applyAllFilters();
    });
    
    maxPriceInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') applyAllFilters();
    });
    
    sortSelect.addEventListener('change', sortProducts);
    
    inStockCheckbox.addEventListener('change', applyAllFilters);
    onSaleCheckbox.addEventListener('change', applyAllFilters);
    newArrivalCheckbox.addEventListener('change', applyAllFilters);
    
    clearFiltersBtn.addEventListener('click', clearAllFilters);
    
    // Debounce function for better performance
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
    
    // Initialize
    updateResultsCount();
    console.log('Sidebar filters initialized successfully');
});

// Additional helper functions
function filterByBadge(badgeType) {
    const productCards = document.querySelectorAll('.product-card');
    let hasMatches = false;
    
    productCards.forEach(card => {
        const hasBadge = card.querySelector(`.${badgeType}`);
        card.style.display = hasBadge ? '' : 'none';
        if (hasBadge) hasMatches = true;
    });
    
    // Update results count
    const visibleCount = Array.from(productCards).filter(card => 
        card.style.display !== 'none'
    ).length;
    const resultsCount = document.querySelector('.results-count');
    if (resultsCount) {
        resultsCount.textContent = `Showing ${visibleCount} of ${productCards.length} products`;
    }
    
    // Show no products message if no matches
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