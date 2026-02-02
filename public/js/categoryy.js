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
    
    // FIXED: Add data attributes to product cards for filtering
    productCards.forEach((card, index) => {
        const productId = card.querySelector('.add-to-cart')?.getAttribute('data-product-id') || index;
        const priceText = card.querySelector('.product-price')?.textContent || '0';
        const price = parseFloat(priceText.replace('₱', '').replace(',', '')) || 0;
        const stockText = card.querySelector('.product-stock')?.textContent || '0';
        const stock = parseInt(stockText.replace(/\D/g, '')) || 0;
        
        // FIXED: Check for both badge types more reliably
        const hasSaleBadge = card.querySelector('.sale-badge') !== null || 
                            card.querySelector('.badge.sale-badge') !== null;
        const hasNewBadge = card.querySelector('.new-arrivals-badge') !== null || 
                           card.querySelector('.badge.new-arrivals-badge') !== null;
        
        // Get category from product card
        const categoryElement = card.querySelector('.product-category');
        const categoryText = categoryElement ? categoryElement.textContent.trim() : '';
        
        card.setAttribute('data-id', productId);
        card.setAttribute('data-price', price);
        card.setAttribute('data-stock', stock);
        card.setAttribute('data-has-sale', hasSaleBadge ? 'true' : 'false');
        card.setAttribute('data-has-new', hasNewBadge ? 'true' : 'false');
        card.setAttribute('data-index', index);
        
        // Log for debugging
        console.log(`Product ${index}: Sale=${hasSaleBadge}, New=${hasNewBadge}, Category="${categoryText}"`);
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
    
    // FIXED: Quick filters with better debugging
    function applyQuickFilters() {
        const inStockOnly = inStockCheckbox ? inStockCheckbox.checked : false;
        const onSaleOnly = onSaleCheckbox ? onSaleCheckbox.checked : false;
        const newArrivalOnly = newArrivalCheckbox ? newArrivalCheckbox.checked : false;
        
        console.log('Quick Filters:', { inStockOnly, onSaleOnly, newArrivalOnly });
        
        // If no quick filters are active, don't filter
        if (!inStockOnly && !onSaleOnly && !newArrivalOnly) {
            return;
        }
        
        let hiddenCount = 0;
        
        productCards.forEach(card => {
            const isCurrentlyVisible = card.style.display !== 'none';
            if (!isCurrentlyVisible) return; // Skip already hidden cards
            
            const stock = parseInt(card.getAttribute('data-stock')) || 0;
            const hasSale = card.getAttribute('data-has-sale') === 'true';
            const hasNew = card.getAttribute('data-has-new') === 'true';
            
            let shouldHide = false;
            
            // Check each active filter
            if (inStockOnly && stock <= 0) {
                shouldHide = true;
            }
            
            if (onSaleOnly && !hasSale) {
                shouldHide = true;
            }
            
            if (newArrivalOnly && !hasNew) {
                shouldHide = true;
                console.log('Hiding product - not new arrival:', card.querySelector('.product-title')?.textContent);
            }
            
            if (shouldHide) {
                card.style.display = 'none';
                hiddenCount++;
            }
        });
        
        console.log(`Quick filters hidden ${hiddenCount} products`);
        updateResultsCount();
    }
    
    // Sort products
    function sortProducts() {
        const sortOption = sortSelect ? sortSelect.value : 'default';
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
        
        // Re-add hidden products to maintain DOM structure
        Array.from(productCards).forEach(card => {
            if (card.style.display === 'none' && !productsGrid.contains(card)) {
                productsGrid.appendChild(card);
            }
        });
    }
    
    // Apply all filters
    function applyAllFilters() {
        console.log('Applying all filters...');
        
        // First, reset all products to visible (except search/category filtered)
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedCategory = document.querySelector('input[name="category"]:checked')?.value || 'all';
        
        // Reset visibility based on search and category only
        productCards.forEach(card => {
            let shouldShow = true;
            
            // Check search
            if (searchTerm) {
                const productName = card.querySelector('.product-title')?.textContent.toLowerCase() || '';
                const productDesc = card.querySelector('.product-description')?.textContent.toLowerCase() || '';
                const productCategory = card.querySelector('.product-category')?.textContent.toLowerCase() || '';
                
                shouldShow = productName.includes(searchTerm) || 
                            productDesc.includes(searchTerm) || 
                            productCategory.includes(searchTerm);
            }
            
            // Check category
            if (shouldShow && selectedCategory !== 'all') {
                const cardCategory = card.getAttribute('data-category');
                shouldShow = cardCategory === selectedCategory;
            }
            
            card.style.display = shouldShow ? '' : 'none';
        });
        
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
        console.log('Clearing all filters...');
        
        // Reset search
        if (searchInput) searchInput.value = '';
        
        // Reset category
        const allCategoriesRadio = document.getElementById('all-categories');
        if (allCategoriesRadio) allCategoriesRadio.checked = true;
        
        // Reset price
        if (minPriceInput) minPriceInput.value = '';
        if (maxPriceInput) maxPriceInput.value = '';
        
        // Reset quick filters
        if (inStockCheckbox) inStockCheckbox.checked = false;
        if (onSaleCheckbox) onSaleCheckbox.checked = false;
        if (newArrivalCheckbox) newArrivalCheckbox.checked = false;
        
        // Reset sort
        if (sortSelect) sortSelect.value = 'default';
        
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
    
    // Event listeners with null checks
    if (searchInput) {
        searchInput.addEventListener('input', debounce(searchProducts, 300));
    }
    
    if (categoryRadios) {
        categoryRadios.forEach(radio => {
            radio.addEventListener('change', filterByCategory);
        });
    }
    
    if (applyPriceBtn) {
        applyPriceBtn.addEventListener('click', function() {
            applyAllFilters();
        });
    }
    
    if (minPriceInput) {
        minPriceInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') applyAllFilters();
        });
    }
    
    if (maxPriceInput) {
        maxPriceInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') applyAllFilters();
        });
    }
    
    if (sortSelect) {
        sortSelect.addEventListener('change', sortProducts);
    }
    
    if (inStockCheckbox) {
        inStockCheckbox.addEventListener('change', applyAllFilters);
    }
    
    if (onSaleCheckbox) {
        onSaleCheckbox.addEventListener('change', applyAllFilters);
    }
    
    if (newArrivalCheckbox) {
        newArrivalCheckbox.addEventListener('change', function() {
            console.log('New Arrival filter toggled:', this.checked);
            applyAllFilters();
        });
    }
    
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }
    
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
    
    // Log which products have New Arrivals badge
    const newArrivalProducts = Array.from(productCards).filter(card => 
        card.getAttribute('data-has-new') === 'true'
    );
    console.log(`Found ${newArrivalProducts.length} products with New Arrivals badge`);
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