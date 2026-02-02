// Combined E-commerce JavaScript - Quick View + Cart Management System



// Quick View Button Logic - Fixed for your setup
document.addEventListener('DOMContentLoaded', function() {
    console.log('Quick view logic initialized');
    
    // Handle quick view button clicks
    document.addEventListener('click', function(e) {
        const quickViewBtn = e.target.closest('.quick-view');
        
        if (quickViewBtn) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Quick view clicked');
            
            // Get product data from the product card
            const productCard = quickViewBtn.closest('.product-card');
            if (!productCard) {
                console.error('Product card not found');
                return;
            }
            
            const productImage = productCard.querySelector('.clickable-image');
            if (!productImage) {
                console.error('Product image not found');
                return;
            }
            
            // Extract product data using getAttribute (matches your PHP attributes)
            const productData = {
                id: productImage.getAttribute('data-product-id') || '',
                name: productImage.getAttribute('data-product-name') || '',
                description: productImage.getAttribute('data-product-description') || '',
                price: productImage.getAttribute('data-product-price') || '0',
                stock: productImage.getAttribute('data-product-stock') || '0',
                category: productImage.getAttribute('data-product-category') || 'Other',
                image: productImage.getAttribute('data-product-image') || '',
                sizes: productImage.getAttribute('data-product-sizes') || ''
            };
            
            console.log('Product data:', productData);
            
            // Validate required data
            if (!productData.id || !productData.name) {
                console.error('Missing required product data');
                return;
            }
            
            // Store product data globally for other functions to access
            window.currentProductData = productData;
            
            // Populate modal using the same logic as clickable images
            populateProductModalComplete(productData);
            
            // Show modal
            const modalElement = document.getElementById('productDetailsModal');
            if (modalElement) {
                if (typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modal.show();
                } else {
                    console.error('Bootstrap not found');
                }
            } else {
                console.error('Modal element not found');
            }
        }
    });
    
    // Comprehensive function to populate modal with product data
    function populateProductModalComplete(data) {
        console.log('Populating modal with complete data:', data);
        
        // Update basic text content
        updateElement('productModalTitle', data.name);
        updateElement('modalProductName', data.name);
        updateElement('modalProductDescription', data.description);
        updateElement('modalProductCategory', data.category);
        
        // Update image
        const modalImage = document.getElementById('modalProductImage');
        if (modalImage) {
            modalImage.src = data.image;
            modalImage.alt = data.name;
            console.log('Updated modal image');
        }
        
        // Update price with proper formatting
        const modalPrice = document.getElementById('modalProductPrice');
        if (modalPrice) {
            const price = parseFloat(data.price) || 0;
            modalPrice.textContent = `₱${price.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
        }
        
        // Update stock with proper status badges
        const stockElement = document.getElementById('modalProductStock');
        if (stockElement) {
            const stockCount = parseInt(data.stock) || 0;
            const productStatus = data.status || 'active';
            
            if (productStatus !== 'active') {
                stockElement.innerHTML = '<span class="badge bg-secondary">Unavailable</span>';
            } else if (stockCount <= 0) {
                stockElement.innerHTML = '<span class="badge bg-danger">Out of Stock</span>';
            } else if (stockCount <= 5) {
                stockElement.innerHTML = `<span class="badge bg-warning text-dark">${stockCount} left in stock</span>`;
            } else {
                stockElement.innerHTML = `<span class="badge bg-success">${stockCount} in stock</span>`;
            }
        }
        
        // Update sizes with interactive buttons
        const sizesContainer = document.getElementById('modalProductSizes');
        if (sizesContainer) {
            sizesContainer.innerHTML = '';
            
            if (data.sizes && data.sizes.trim() !== '') {
                const availableSizes = data.sizes.split(',');
                
                availableSizes.forEach(size => {
                    const trimmedSize = size.trim();
                    if (trimmedSize) {
                        const sizeBtn = document.createElement('button');
                        sizeBtn.className = 'btn btn-outline-dark me-2 mb-2';
                        sizeBtn.textContent = trimmedSize;
                        sizeBtn.onclick = function() {
                            // Remove active state from all size buttons
                            sizesContainer.querySelectorAll('.btn').forEach(btn => {
                                btn.classList.remove('btn-primary');
                                btn.classList.add('btn-outline-dark');
                            });
                            // Add active state to clicked button
                            this.classList.remove('btn-outline-dark');
                            this.classList.add('btn-primary');
                        };
                        sizesContainer.appendChild(sizeBtn);
                    }
                });
            } else {
                sizesContainer.innerHTML = '<span class="text-muted">One size fits all</span>';
            }
        }
        
        // Update Add to Cart button state
        const addToCartBtn = document.querySelector('#productDetailsModal .btn[onclick*="Add"], #productDetailsModal button:contains("Add to Cart")') || 
                           document.querySelector('#productDetailsModal .btn-primary:last-of-type');
        
        if (addToCartBtn) {
            const stockCount = parseInt(data.stock) || 0;
            const productStatus = data.status || 'active';
            
            addToCartBtn.disabled = productStatus !== 'active' || stockCount <= 0;
            
            if (addToCartBtn.disabled) {
                addToCartBtn.innerHTML = productStatus !== 'active' ? 
                    '<i class="fas fa-ban me-2"></i>Product Unavailable' : 
                    '<i class="fas fa-times me-2"></i>Out of Stock';
            } else {
                addToCartBtn.innerHTML = '<i class="fas fa-cart-plus me-2"></i>Add to Cart';
            }
        }
        
        // Clear alternative images section
        const altImagesContainer = document.getElementById('modalAltImages');
        if (altImagesContainer) {
            altImagesContainer.innerHTML = '<small class="text-muted">No alternative images available</small>';
        }
        
        console.log('Modal populated successfully with complete data');
    }
    
    // Helper function to safely update element text content
    function updateElement(id, content) {
        const element = document.getElementById(id);
        if (element && content) {
            element.textContent = content;
            console.log(`Updated ${id} with: ${content}`);
        } else if (!element) {
            console.warn(`Element with id '${id}' not found`);
        }
    }

    // Mobile sidebar toggle
    const mobileFilterToggle = document.getElementById('mobileFilterToggle');
    if (mobileFilterToggle) {
        mobileFilterToggle.addEventListener('click', function() {
            const sidebar = document.getElementById('shopSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.add('active');
            if (overlay) overlay.classList.add('active');
        });
    }

    const closeSidebar = document.getElementById('closeSidebar');
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            const sidebar = document.getElementById('shopSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
        });
    }

    const sidebarOverlay = document.getElementById('sidebarOverlay');
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            const sidebar = document.getElementById('shopSidebar');
            if (sidebar) sidebar.classList.remove('active');
            this.classList.remove('active');
        });
    }

    // View toggle
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const view = this.getAttribute('data-view');
            const grid = document.getElementById('productsGrid');
            
            if (grid) {
                if (view === 'list') {
                    grid.classList.add('list-view');
                } else {
                    grid.classList.remove('list-view');
                }
            }
        });
    });
});

// Fixed Cart Management System with Enhanced Stock Management
class CartManager {
    constructor() {
        this.cart = [];
        this.loadCartFromStorage();
        this.updateCartDisplay();
        this.initializeEventListeners();
    }

    // Load cart from localStorage (Note: Using memory storage in Claude artifacts)
    loadCartFromStorage() {
        try {
            // In Claude artifacts, we'll use a temporary cart variable instead of localStorage
            if (window.tempCart) {
                this.cart = window.tempCart;
            } else {
                this.cart = [];
            }
        } catch (error) {
            console.error('Error loading cart from storage:', error);
            this.cart = [];
        }
    }

    // Save cart to storage (Note: Using memory storage in Claude artifacts)
    saveCartToStorage() {
        try {
            // In Claude artifacts, we'll save to a temporary variable instead of localStorage
            window.tempCart = this.cart;
        } catch (error) {
            console.error('Error saving cart to storage:', error);
        }
    }

    // NEW: Refresh product stock displays on the page
    refreshProductStockDisplays() {
        console.log('Refreshing product stock displays...');
        
        // Method 1: Trigger product availability checker
        if (window.productChecker) {
            window.productChecker.checkProductAvailability();
        }
        
        // Method 2: Directly update product cards (faster)
        document.querySelectorAll('.product-card').forEach(card => {
            const productId = card.querySelector('.product-image')?.dataset.productId;
            const stockBadge = card.querySelector('.stock-badge');
            const addButton = card.querySelector('.add-to-cart');
            
            if (productId && stockBadge) {
                // You could make an AJAX call to get updated stock for this product
                // Or just show a "loading" state until the next full refresh
                stockBadge.innerHTML = '<span class="badge bg-info">Updating...</span>';
                
                if (addButton) {
                    addButton.disabled = true;
                    addButton.innerHTML = '<i class="fas fa-sync fa-spin"></i> Updating';
                }
            }
        });
        
        // Method 3: Reload specific product data after a short delay
        setTimeout(() => {
            if (window.productChecker) {
                window.productChecker.checkProductAvailability();
            }
        }, 1000);
    }

    // Enhanced stock validation function
    validateStock(productData, requestedQuantity = 1, selectedSize = null) {
        const validation = {
            isValid: false,
            availableStock: 0,
            message: '',
            errorType: null,
            productStatus: 'unknown'
        };

        // Check if product is active first
        if (productData.status && productData.status !== 'active') {
            validation.message = 'This product is currently unavailable';
            validation.errorType = 'PRODUCT_INACTIVE';
            validation.productStatus = productData.status;
            return validation;
        }

        // Add size-specific validation if available
        let currentStock = parseInt(productData.stock) || 0;
        if (selectedSize && productData.stock_json) {
            try {
                const stockData = JSON.parse(productData.stock_json);
                if (stockData[selectedSize] !== undefined) {
                    currentStock = stockData[selectedSize];
                }
            } catch (e) {
                console.error('Error parsing stock JSON:', e);
            }
        }

        validation.availableStock = currentStock;

        if (currentStock <= 0) {
            validation.message = 'This item is currently out of stock';
            validation.errorType = 'OUT_OF_STOCK';
            return validation;
        }

        const existingItem = this.cart.find(item => 
            item.id === productData.id && item.size === selectedSize
        );
        const currentCartQuantity = existingItem ? existingItem.quantity : 0;
        const totalRequestedQuantity = currentCartQuantity + requestedQuantity;
        
        if (totalRequestedQuantity > currentStock) {
            const remainingStock = currentStock - currentCartQuantity;
            
            if (remainingStock <= 0) {
                validation.message = `You already have the maximum available quantity (${currentStock}) in your cart`;
                validation.errorType = 'MAX_IN_CART';
            } else {
                validation.message = `Only ${remainingStock} more item(s) can be added to cart`;
                validation.errorType = 'INSUFFICIENT_STOCK';
            }
            return validation;
        }

        validation.isValid = true;
        validation.message = 'Stock available';
        validation.productStatus = 'active';
        return validation;
    }

    addToCart(productData, selectedSize = null, requestedQuantity = 1) {
        // Use the original price directly (no multiplication)
        const price = parseFloat(productData.price);
        console.log('Adding to cart - Product:', productData.name, 'Price:', price); // Debug
        
        const stockValidation = this.validateStock({
            ...productData,
            price: price
        }, requestedQuantity, selectedSize);
        
        if (!stockValidation.isValid) {
            this.showStockError(productData.name, stockValidation.message, stockValidation.errorType);
            return false;
        }

        const existingItemIndex = this.cart.findIndex(item => 
            item.id === productData.id && item.size === selectedSize
        );

        if (existingItemIndex > -1) {
            this.cart[existingItemIndex].quantity += requestedQuantity;
        } else {
            this.cart.push({
                id: productData.id,
                name: productData.name,
                price: price, // Use the original price (no multiplication)
                image: productData.image,
                size: selectedSize,
                category: productData.category,
                quantity: requestedQuantity,
                maxStock: parseInt(productData.stock) || 0,
                productStatus: productData.status || 'active',
                stock_json: productData.stock_json || null
            });
        }

        this.updateCartDisplay();
        this.saveCartToStorage();
        this.showCartNotification(productData.name, requestedQuantity);
        return true;
    }

    // Remove product from cart
    removeFromCart(productId, size = null) {
        this.cart = this.cart.filter(item => 
            !(item.id === productId && item.size === size)
        );
        this.updateCartDisplay();
        this.saveCartToStorage();
    }

    // Calculate total
    getTotal() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    // Get total items count
    getTotalItems() {
        return this.cart.reduce((total, item) => total + item.quantity, 0);
    }

    // Update cart display in modal and header
    updateCartDisplay() {
        this.updateCartModal();
        this.updateCartBadge();
    }

    // Enhanced cart modal with stock info
    updateCartModal() {
        const cartItems = document.querySelector('#cartItems');
        const emptyCartMessage = document.querySelector('#emptyCartMessage');
        const cartSummary = document.querySelector('#cartSummary');

        if (!cartItems) return;

        if (this.cart.length === 0) {
            if (emptyCartMessage) emptyCartMessage.style.display = 'block';
            if (cartSummary) cartSummary.style.display = 'none';
            cartItems.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-4" style="opacity: 0.3;"></i>
                    <h6 class="text-muted mb-2">Your cart is empty</h6>
                    <p class="text-muted small">Add some products to get started!</p>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-store me-1"></i>Continue Shopping
                    </button>
                </div>
            `;
            return;
        }

        if (emptyCartMessage) emptyCartMessage.style.display = 'none';
        if (cartSummary) cartSummary.style.display = 'block';

        let cartHTML = '';
        this.cart.forEach(item => {
            const stockWarning = item.quantity >= item.maxStock ? 
                `<small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Max stock reached</small>` : 
                item.maxStock <= 5 ? 
                `<small class="text-info">Only ${item.maxStock} available</small>` : '';

            const statusWarning = item.productStatus !== 'active' ? 
                `<small class="text-danger"><i class="fas fa-ban"></i> Product no longer available</small>` : '';

            cartHTML += `
                <div class="cart-item mb-3 p-3 border rounded" data-product-id="${item.id}" data-size="${item.size || ''}" data-max-stock="${item.maxStock}">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="${item.image}" alt="${item.name}" class="img-fluid rounded" style="max-height: 60px;">
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-1">${item.name}</h6>
                            <small class="text-muted">
                                ${item.size ? `Size: ${item.size}` : 'One Size'}
                                ${item.category ? ` | ${item.category}` : ''}
                            </small>
                            ${stockWarning ? `<br>${stockWarning}` : ''}
                            ${statusWarning ? `<br>${statusWarning}` : ''}
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary quantity-btn" data-action="decrease" type="button" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                                <input type="text" class="form-control text-center quantity-input" value="${item.quantity}" readonly>
                                <button class="btn btn-outline-secondary quantity-btn" data-action="increase" type="button" ${item.quantity >= item.maxStock ? 'disabled' : ''}>+</button>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <strong>₱${(item.price * item.quantity).toFixed(2)}</strong>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-outline-danger btn-sm remove-item" title="Remove from cart">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        cartItems.innerHTML = cartHTML;
    }

    // Update cart badge and all totals
    updateCartBadge() {
        const cartBadge = document.querySelector('.cart-count');
        const cartItemCount = document.querySelector('#cartItemCount');
        
        // Update checkout modal
        const checkoutTotalDisplay = document.querySelector('#checkoutTotalDisplay');
        const checkoutTotal = document.querySelector('#checkoutTotal');
        
        const cartTotal = document.querySelector('#cartTotal');
        const summaryItemCount = document.querySelector('#summaryItemCount');
        const summarySubtotal = document.querySelector('#summarySubtotal');
        
        const totalItems = this.getTotalItems();
        const totalPrice = this.getTotal();
        
        // Update header badge
        if (cartBadge) {
            cartBadge.textContent = totalItems;
            cartBadge.style.display = totalItems > 0 ? 'block' : 'none';
        }
        
        // Update modal header badge
        if (cartItemCount) {
            cartItemCount.textContent = totalItems;
        }
        
        // Update both possible checkout total elements
        if (checkoutTotalDisplay) {
            checkoutTotalDisplay.textContent = '₱' + totalPrice.toFixed(2);
        }
        if (checkoutTotal) {
            checkoutTotal.textContent = totalPrice.toFixed(2);
        }
        
        // Update main cart total
        if (cartTotal) {
            cartTotal.textContent = totalPrice.toFixed(2);
        }
        
        // Update summary section
        if (summaryItemCount) {
            summaryItemCount.textContent = totalItems;
        }
        if (summarySubtotal) {
            summarySubtotal.textContent = totalPrice.toFixed(2);
        }
        
        // Show/hide summary section and buttons
        const cartSummary = document.querySelector('#cartSummary');
        const checkoutBtn = document.querySelector('#checkoutBtn');
        
        if (cartSummary) {
            cartSummary.style.display = totalItems > 0 ? 'block' : 'none';
        }
        if (checkoutBtn) {
            checkoutBtn.disabled = totalItems === 0;
        }
    }

    // Enhanced proceed to checkout
    proceedToCheckout() {
        console.log('Proceeding to checkout...'); // Debug log
        
        if (this.cart.length === 0) {
            alert('Your cart is empty!');
            return;
        }
        
        // Validate stock before checkout
        const invalidItems = this.validateCartStock();
        if (invalidItems.length > 0) {
            alert('Some items in your cart exceed available stock.');
            return;
        }
        
        this.updateCheckoutDisplay();
        
        const checkoutModalElement = document.getElementById('checkoutModal');
        if (checkoutModalElement) {
            try {
                const checkoutModal = new bootstrap.Modal(checkoutModalElement);
                checkoutModal.show();
            } catch (error) {
                console.error('Error showing checkout modal:', error);
                alert('Unable to open checkout. Please try again.');
            }
        } else {
            console.error('Checkout modal not found!');
            alert('Checkout system error. Please refresh the page and try again.');
        }
    }

    updateCheckoutDisplay() {
        console.log('Updating checkout display...', this.cart);
        
        this.updateCheckoutItems();
        
        const totalPrice = this.getTotal();
        const checkoutTotalDisplay = document.getElementById('checkoutTotalDisplay');
        
        if (checkoutTotalDisplay) {
            checkoutTotalDisplay.textContent = '₱' + totalPrice.toFixed(2);
            console.log('Updated checkout total:', totalPrice); 
        } else {
            console.error('checkoutTotalDisplay element not found!');
        }
    }

    updateCheckoutItems() {
        const checkoutItems = document.getElementById('checkoutItems');
        if (!checkoutItems) {
            console.error('checkoutItems element not found!');
            return;
        }
        
        console.log('Updating checkout items...', this.cart); 
        
        if (this.cart.length === 0) {
            checkoutItems.innerHTML = '<p class="text-muted">No items in cart</p>';
            return;
        }
        
        checkoutItems.innerHTML = '';
        
        this.cart.forEach(item => {
            const itemHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                    <div class="flex-grow-1">
                        <div class="fw-bold">${item.name}</div>
                        <small class="text-muted">
                            ${item.size ? `Size: ${item.size}` : 'One Size'} | 
                            Qty: ${item.quantity} × ₱${item.price.toFixed(2)}
                        </small>
                    </div>
                    <div class="text-end">
                        <strong>₱${(item.price * item.quantity).toFixed(2)}</strong>
                    </div>
                </div>
            `;
            checkoutItems.innerHTML += itemHTML;
        });
        
        console.log('Checkout items updated successfully');
    }

    validateCartStock() {
        const invalidItems = [];
        
        this.cart.forEach(item => {
            if (item.quantity > item.maxStock || item.productStatus !== 'active') {
                invalidItems.push({
                    ...item,
                    excessQuantity: item.quantity - item.maxStock,
                    reason: item.productStatus !== 'active' ? 'Product unavailable' : 'Insufficient stock'
                });
            }
        });
        
        return invalidItems;
    }

    updateQuantity(productId, size, newQuantity) {
        const itemIndex = this.cart.findIndex(item => 
            item.id === productId && item.size === size
        );
        
        if (itemIndex > -1) {
            if (newQuantity <= 0) {
                this.removeFromCart(productId, size);
            } else {
                if (newQuantity > this.cart[itemIndex].maxStock) {
                    this.showStockError('Quantity Update', `Maximum available quantity is ${this.cart[itemIndex].maxStock}`, 'QUANTITY_EXCEEDED');
                    return false;
                }
                
                this.cart[itemIndex].quantity = newQuantity;
                this.updateCartDisplay();
                this.saveCartToStorage();
            }
        }
        return true;
    }

    closeAllModals() {
        const modals = ['checkoutModal', 'cartModal', 'productDetailsModal'];
        
        modals.forEach(modalId => {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                try {
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    
                    // Force remove backdrop if it exists
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                    
                    // Reset body classes
                    document.body.classList.remove('modal-open');
                    document.body.style.paddingRight = '';
                    document.body.style.overflow = '';
                    
                } catch (error) {
                    console.error('Error closing modal:', modalId, error);
                }
            }
        });
    }

    // Show notifications
    showCartNotification(productName, quantity = 1) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>${quantity} ${productName}${quantity > 1 ? 's' : ''}</strong> added to cart!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        this.showToast(toast);
    }

    showStockError(productName, message, errorType) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        
        let iconClass = 'fas fa-exclamation-triangle';
        let alertClass = 'alert-warning';
        
        if (errorType === 'OUT_OF_STOCK' || errorType === 'STOCK_DEPLETED' || errorType === 'PRODUCT_INACTIVE') {
            iconClass = 'fas fa-times-circle';
            alertClass = 'alert-danger';
        }
        
        toast.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${iconClass} me-2"></i>
                <strong>${productName}:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        this.showToast(toast, 5000);
    }

    // Handle stock errors from server response
    handleStockErrors(errorData) {
        if (errorData.message && errorData.message.includes('stock')) {
            // Just show the error message without trying to update local stock
            this.showStockError(
                'Stock Error', 
                'There was a stock issue with your order. Please check your cart and try again.',
                'STOCK_ERROR'
            );
            
            // Refresh cart display to ensure UI is updated
            this.updateCartDisplay();
        }
    }

    // Show transaction error
    showTransactionError() {
        const toast = document.createElement('div');
        toast.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Transaction Failed:</strong> Your order could not be completed due to a system error. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        this.showToast(toast, 5000);
    }

    // Show order success with details
    showOrderSuccess(orderId, updatedProducts) {
        let message = `Order #${orderId} placed successfully!`;
        
        // Create a nice alert instead of default alert
        const successModal = document.createElement('div');
        successModal.className = 'modal fade';
        successModal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Order Successful</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue Shopping</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(successModal);
        const modal = new bootstrap.Modal(successModal);
        modal.show();
        
        // Clean up after modal is hidden
        successModal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(successModal);
        });
    }

    // Redirect to login page
    redirectToLogin() {
        // Save current cart and redirect (simulated for Claude artifacts)
        window.location.href = '/login.php';
    }

    // Show/hide loading state
    showLoadingState() {
        const loader = document.getElementById('globalLoader') || this.createLoader();
        loader.style.display = 'flex';
        document.body.style.pointerEvents = 'none';
    }

    hideLoadingState() {
        const loader = document.getElementById('globalLoader');
        if (loader) {
            loader.style.display = 'none';
        }
        document.body.style.pointerEvents = '';
    }

    createLoader() {
        const loader = document.createElement('div');
        loader.id = 'globalLoader';
        loader.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        `;
        loader.innerHTML = `
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
        document.body.appendChild(loader);
        return loader;
    }

    showToast(toast, duration = 3000) {
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.minWidth = '300px';
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, duration);
    }

    // Initialize event listeners
    initializeEventListeners() {
        // Add to cart buttons in product grid
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
                e.preventDefault();
                const button = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
                const productCard = button.closest('.product-card');
                const productImage = productCard.querySelector('.product-image');
                
                if (productImage) {
                    const productData = {
                        id: productImage.dataset.productId,
                        name: productImage.dataset.productName,
                        price: productImage.dataset.productPrice,
                        image: productImage.dataset.productImage,
                        category: productImage.dataset.productCategory,
                        stock: productImage.dataset.productStock || '0',
                        status: productImage.dataset.productStatus || 'active',
                        stock_json: productImage.dataset.productStockJson || null
                    };
                    
                    this.addToCart(productData);
                }
            }
        });

        // Add to cart button in modal
        document.addEventListener('click', (e) => {
            if (e.target.textContent.includes('Add to Cart') && e.target.closest('#productDetailsModal')) {
                e.preventDefault();
                
                const selectedSizeBtn = document.querySelector('#modalProductSizes .btn-primary');
                const selectedSize = selectedSizeBtn ? selectedSizeBtn.textContent : null;
                
                const productData = {
                    id: window.currentProductData.id,
                    name: window.currentProductData.name,
                    price: window.currentProductData.price,
                    image: window.currentProductData.image,
                    category: window.currentProductData.category,
                    stock: window.currentProductData.stock || '0',
                    status: window.currentProductData.status || 'active',
                    stock_json: window.currentProductData.stock_json || null
                };
                
                if (this.addToCart(productData, selectedSize)) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('productDetailsModal'));
                    if (modal) modal.hide();
                }
            }
        });

        // Cart modal event listeners
        document.addEventListener('click', (e) => {
            const cartItem = e.target.closest('.cart-item');
            if (!cartItem) return;
            
            const productId = cartItem.dataset.productId;
            const size = cartItem.dataset.size || null;
            const maxStock = parseInt(cartItem.dataset.maxStock) || 0;
            
            if (e.target.classList.contains('quantity-btn')) {
                const action = e.target.dataset.action;
                const quantityInput = cartItem.querySelector('.quantity-input');
                const currentQuantity = parseInt(quantityInput.value);
                
                if (action === 'increase' && currentQuantity < maxStock) {
                    this.updateQuantity(productId, size, currentQuantity + 1);
                } else if (action === 'decrease') {
                    this.updateQuantity(productId, size, currentQuantity - 1);
                }
            }
            
            if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
                this.removeFromCart(productId, size);
            }
        });

        // Checkout button event listener
        document.addEventListener('click', (e) => {
            if (e.target.id === 'checkoutBtn' || e.target.closest('#checkoutBtn')) {
                console.log('Checkout button clicked'); // Debug log
                this.proceedToCheckout();
            }
        });
    }
}

// Product Availability Checker Class
class ProductAvailabilityChecker {
    constructor() {
        this.lastChecked = 0;
        this.checkInterval = 30000; // Check every 30 seconds
    }

    async checkProductAvailability() {
        const now = Date.now();
        if (now - this.lastChecked < this.checkInterval) return;
        
        this.lastChecked = now;
        
        try {
            const response = await fetch('/e-commerce/app/controllers/ProductController.php?action=check_availability');
            
            // Check if response is valid before parsing JSON
            if (!response.ok) {
                console.error('Product availability check failed with status:', response.status);
                return;
            }
            
            const text = await response.text();
            if (!text.trim()) {
                console.error('Product availability check returned empty response');
                return;
            }
            
            const data = JSON.parse(text);
            
            if (data.success) {
                this.updateProductDisplays(data.products);
                this.validateCartAgainstServer(data.products);
            }
        } catch (error) {
            console.error('Product availability check failed:', error);
        }
    }

    updateProductDisplays(products) {
        // Update product cards with current status
        document.querySelectorAll('.product-card').forEach(card => {
            const productId = card.querySelector('.product-image')?.dataset.productId;
            if (!productId) return;
            
            const product = products.find(p => p.id == productId);
            if (!product) return;
            
            const stockBadge = card.querySelector('.stock-badge');
            const addButton = card.querySelector('.add-to-cart');
            
            if (stockBadge) {
                if (product.status !== 'active') {
                    stockBadge.innerHTML = '<span class="badge bg-secondary">Unavailable</span>';
                } else if (product.stock <= 0) {
                    stockBadge.innerHTML = '<span class="badge bg-danger">Out of Stock</span>';
                } else if (product.stock <= 5) {
                    stockBadge.innerHTML = `<span class="badge bg-warning text-dark">${product.stock} left</span>`;
                } else {
                    stockBadge.innerHTML = `<span class="badge bg-success">In Stock</span>`;
                }
            }
            
            if (addButton) {
                addButton.disabled = product.stock <= 0 || product.status !== 'active';
                if (addButton.disabled) {
                    addButton.innerHTML = product.status !== 'active' ? 
                        '<i class="fas fa-ban"></i> Unavailable' : 
                        '<i class="fas fa-times"></i> Out of Stock';
                } else {
                    addButton.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
                }
            }
        });
    }

    validateCartAgainstServer(products) {
        if (!window.cartManager) return;
        
        const cart = window.cartManager.cart;
        const updatesNeeded = [];
        
        cart.forEach(item => {
            const product = products.find(p => p.id == item.id);
            if (!product) return;
            
            // Check if product is now inactive or stock changed
            if (product.status !== 'active' || product.stock < item.quantity) {
                updatesNeeded.push({
                    item,
                    availableStock: product.stock,
                    isActive: product.status === 'active'
                });
            }
        });
        
        if (updatesNeeded.length > 0) {
            this.handleCartUpdates(updatesNeeded);
        }
    }

    handleCartUpdates(updates) {
        updates.forEach(update => {
            if (update.availableStock <= 0 || !update.isActive) {
                // Remove completely unavailable items
                window.cartManager.removeFromCart(update.item.id, update.item.size);
                window.cartManager.showStockError(
                    update.item.name, 
                    'This item is no longer available and was removed from your cart',
                    'STOCK_DEPLETED'
                );
            } else if (update.item.quantity > update.availableStock) {
                // Adjust quantity for limited stock
                window.cartManager.updateQuantity(
                    update.item.id, 
                    update.item.size, 
                    update.availableStock
                );
                window.cartManager.showStockError(
                    update.item.name,
                    `Stock reduced to ${update.availableStock} (previously ${update.item.quantity})`,
                    'STOCK_REDUCED'
                );
            }
        });
        
        window.cartManager.updateCartDisplay();
    }
}

// Check if Bootstrap is available
if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap is not loaded! Modal functionality will not work.');
}

// Enhanced checkout form submission with proper modal handling
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate cart before submission
            if (!window.cartManager || window.cartManager.cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            console.log('Submitting order...', window.cartManager.cart);
            
            // Show loading state
            const submitBtn = checkoutForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing Order...';
            submitBtn.disabled = true;
            
            window.cartManager.showLoadingState();
            
            // Prepare form data
            const formData = new FormData(checkoutForm);
            
            // Add cart data and total
            formData.append('cart', JSON.stringify(window.cartManager.cart));
            formData.append('total_amount', window.cartManager.getTotal().toFixed(2));
            
            // Debug: Log the data being sent
            console.log('Form Data:', {
                cart: window.cartManager.cart,
                total_amount: window.cartManager.getTotal(),
                full_name: formData.get('full_name'),
                phone: formData.get('phone'),
                shipping_address: formData.get('shipping_address'),
                payment_method: formData.get('payment_method'),
                special_instructions: formData.get('special_instructions')
            });
            
            // Use absolute path to avoid 404 errors
            const fetchURL = '/e-commerce/app/controllers/OrderController.php?action=placeOrder';
            console.log('Fetching URL:', fetchURL);
            
            // Make the request
            fetch(fetchURL, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                
                // Handle authentication errors
                if (response.status === 401 || response.status === 403) {
                    window.cartManager.redirectToLogin();
                    throw new Error('Please login first');
                }
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
            })
            .then(data => {
                console.log('Processed response:', data);
                
                if (data.success) {
                    // Success - clear cart and show success message
                    window.cartManager.cart = [];
                    window.cartManager.saveCartToStorage();
                    window.cartManager.updateCartDisplay();
                    
                    // Reload after showing success message
                    setTimeout(() => {
                        location.reload();
                    }, 5000);
                    
                    // Show success message with order details
                    window.cartManager.showOrderSuccess(data.order_id, data.updated_products);
                    window.cartManager.closeAllModals();
                }
            })
            .catch(error => {
                console.error('Checkout error:', error);
                
                // Show transaction error
                window.cartManager.showTransactionError();
                
                // Show more specific error messages
                if (error.message.includes('Failed to fetch')) {
                    console.error('Network error:', error);
                } else if (error.message.includes('Invalid JSON')) {
                    console.error('Server returned invalid JSON:', error);
                } else {
                    console.error('Unexpected error:', error);
                    window.cartManager.showStockError(
                        'Checkout Error',
                        'Please try again or contact support if the problem persists.',
                        'CHECKOUT_ERROR'
                    );
                }
            })
            .finally(() => {
                // Always reset button state and ensure modals are closed
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                window.cartManager.hideLoadingState();
                
                // Force close modals in case of any error
                setTimeout(() => {
                    window.cartManager.closeAllModals();
                }, 100);
            });
        });
    }

    // Initialize cart manager
    console.log('Initializing cart manager...'); // Debug log
    window.cartManager = new CartManager();
    
    // Initialize product availability checker
    window.productChecker = new ProductAvailabilityChecker();
    
    // Start checking product availability
    setInterval(() => {
        window.productChecker.checkProductAvailability();
    }, 30000); // Check every 30 seconds
    
    // Initial check
    setTimeout(() => {
        window.productChecker.checkProductAvailability();
    }, 1000);
    
    // Initialize product modal functionality
    const productModalElement = document.getElementById('productDetailsModal');
    if (productModalElement) {
        const productModal = new bootstrap.Modal(productModalElement);
        const clickableImages = document.querySelectorAll('.clickable-image');
        
        clickableImages.forEach(image => {
            image.addEventListener('click', function() {
                const productData = {
                    id: this.dataset.productId,
                    name: this.dataset.productName,
                    description: this.dataset.productDescription,
                    price: this.dataset.productPrice,
                    stock: this.dataset.productStock,
                    status: this.dataset.productStatus || 'active',
                    category: this.dataset.productCategory,
                    image: this.dataset.productImage,
                    sizes: this.dataset.productSizes || '',
                    stock_json: this.dataset.productStockJson || null
                };
                
                window.currentProductData = productData;
                
                document.getElementById('modalProductName').textContent = productData.name;
                document.getElementById('modalProductDescription').textContent = productData.description;
                document.getElementById('modalProductPrice').textContent = '₱' + productData.price;
                
                const stockElement = document.getElementById('modalProductStock');
                const stockCount = parseInt(productData.stock) || 0;
                const productStatus = productData.status || 'active';
                
                if (productStatus !== 'active') {
                    stockElement.innerHTML = '<span class="badge bg-secondary">Unavailable</span>';
                } else if (stockCount <= 0) {
                    stockElement.innerHTML = '<span class="badge bg-danger">Out of Stock</span>';
                } else if (stockCount <= 5) {
                    stockElement.innerHTML = `<span class="badge bg-warning text-dark">${stockCount} left in stock</span>`;
                } else {
                    stockElement.innerHTML = `<span class="badge bg-success">${stockCount} in stock</span>`;
                }
                
                document.getElementById('modalProductCategory').textContent = productData.category;
                document.getElementById('modalProductImage').src = productData.image;
                document.getElementById('modalProductImage').alt = productData.name;
                
                const sizesContainer = document.getElementById('modalProductSizes');
                sizesContainer.innerHTML = '';
                
                if (productData.sizes && productData.sizes.trim() !== '') {
                    const availableSizes = productData.sizes.split(',');
                    
                    availableSizes.forEach(size => {
                        const trimmedSize = size.trim();
                        if (trimmedSize) {
                            const sizeBtn = document.createElement('button');
                            sizeBtn.className = 'btn btn-outline-dark me-2 mb-2';
                            sizeBtn.textContent = trimmedSize;
                            sizeBtn.onclick = function() {
                                sizesContainer.querySelectorAll('.btn').forEach(btn => {
                                    btn.classList.remove('btn-primary');
                                    btn.classList.add('btn-outline-dark');
                                });
                                this.classList.remove('btn-outline-dark');
                                this.classList.add('btn-primary');
                            };
                            sizesContainer.appendChild(sizeBtn);
                        }
                    });
                } else {
                    sizesContainer.innerHTML = '<span class="text-muted">One size fits all</span>';
                }

                // Disable add to cart button if unavailable
                const addToCartBtn = document.querySelector('#productDetailsModal .btn-primary');
                if (addToCartBtn) {
                    addToCartBtn.disabled = productStatus !== 'active' || stockCount <= 0;
                    if (addToCartBtn.disabled) {
                        addToCartBtn.innerHTML = productStatus !== 'active' ? 
                            'Product Unavailable' : 'Out of Stock';
                    } else {
                        addToCartBtn.innerHTML = 'Add to Cart';
                    }
                }

                document.getElementById('modalAltImages').innerHTML = '<small class="text-muted">No alternative images available</small>';
                
                productModal.show();
            });
        });
    }

    // Check for login success parameter
    const urlParams = new URLSearchParams(window.location.search);
    const successParam = urlParams.get('success');
    
    if (successParam === 'login_success') {
        // Show login success modal
        const loginSuccessModal = new bootstrap.Modal(document.getElementById('loginSuccessModal'));
        loginSuccessModal.show();
        
        // Clean the URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});


