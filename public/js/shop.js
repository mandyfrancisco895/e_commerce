document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing shop functionality...');
    
    // ========================================
    // SEARCH AND FILTER FUNCTIONALITY
    // ========================================
    const searchInput = document.querySelector('.search-box input');
    const categoryFilter = document.querySelector('.filter-select');
    const sortFilter = document.querySelectorAll('.filter-select')[1];
    const productsGrid = document.getElementById('productsGrid');
    const productCards = document.querySelectorAll('.product-card');
    
    if (searchInput && categoryFilter && sortFilter && productsGrid && productCards.length > 0) {
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
            const visibleProducts = Array.from(productCards).filter(card => 
                card.style.display !== 'none'
            );
            
            if (visibleProducts.length === 0) return;
            
            switch(sortOption) {
                case 'Price: Low to High':
                    visibleProducts.sort((a, b) => parseFloat(a.getAttribute('data-price')) - parseFloat(b.getAttribute('data-price')));
                    break;
                case 'Price: High to Low':
                    visibleProducts.sort((a, b) => parseFloat(b.getAttribute('data-price')) - parseFloat(a.getAttribute('data-price')));
                    break;
                case 'Newest First':
                case 'Most Popular':
                    visibleProducts.sort((a, b) => parseInt(b.getAttribute('data-id')) - parseInt(a.getAttribute('data-id')));
                    break;
                default:
                    visibleProducts.sort((a, b) => parseInt(a.getAttribute('data-index')) - parseInt(b.getAttribute('data-index')));
            }
            
            visibleProducts.forEach(product => productsGrid.appendChild(product));
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
        categoryFilter.addEventListener('change', filterByCategory);
        sortFilter.addEventListener('change', sortProducts);
        
        console.log('Search and filter initialized successfully');
    }

    // ========================================
    // SHOPPING CART FUNCTIONALITY - UNIFIED
    // ========================================
    
    // FIXED: Use the same cart storage as CartManager in pages.js
    function getCart() {
        try {
            // Try localStorage first (same as CartManager would use in production)
            const stored = localStorage.getItem('shopping_cart');
            if (stored) {
                return JSON.parse(stored);
            }
            // Fallback to window.tempCart (for CartManager compatibility)
            return window.tempCart || [];
        } catch (error) {
            console.error('Error getting cart:', error);
            return [];
        }
    }
    
    function saveCart(cart) {
        try {
            // Save to BOTH locations to ensure compatibility
            localStorage.setItem('shopping_cart', JSON.stringify(cart));
            window.tempCart = cart;
            
            // Also update CartManager if it exists
            if (window.cartManager) {
                window.cartManager.cart = cart;
                window.cartManager.updateCartDisplay();
            }
        } catch (error) {
            console.error('Error saving cart:', error);
        }
    }
    
    function updateCartCount() {
        const cart = getCart();
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        
        const cartBadges = document.querySelectorAll('.cart-count, .badge');
        cartBadges.forEach(badge => {
            if (badge.closest('[data-bs-toggle="modal"][data-bs-target="#cartModal"]') || 
                badge.closest('.cart-icon')) {
                badge.textContent = totalItems;
                badge.style.display = totalItems > 0 ? 'inline-block' : 'none';
            }
        });
    }
    
    function getCartTotal() {
        const cart = getCart();
        return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }
    
    // Update cart count on page load
    updateCartCount();
    
    // ========================================
    // CHECKOUT MODAL POPULATION
    // ========================================
    
    const checkoutModal = document.getElementById('checkoutModal');
    if (checkoutModal) {
        checkoutModal.addEventListener('show.bs.modal', function() {
            populateCheckoutModal();
        });
    }
    
    function populateCheckoutModal() {
        const cart = getCart();
        const checkoutItemsContainer = document.getElementById('checkoutItems');
        const checkoutTotalDisplay = document.getElementById('checkoutTotal');
        
        if (!checkoutItemsContainer || !checkoutTotalDisplay) {
            console.error('Checkout elements not found');
            return;
        }
        
        // Check if cart is empty
        if (cart.length === 0) {
            alert('Your cart is empty!');
            const modal = bootstrap.Modal.getInstance(checkoutModal);
            if (modal) modal.hide();
            return;
        }
        
        // Clear existing items
        checkoutItemsContainer.innerHTML = '';
        
        // Add each cart item to checkout modal
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            const itemHtml = `
                <div class="checkout-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center flex-grow-1">
                            <img src="${item.image}" alt="${item.name}" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                            <div>
                                <strong>${item.name}</strong>
                                <div class="small text-muted">Size: ${item.size} | Qty: ${item.quantity}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-success fw-bold">₱${itemTotal.toFixed(2)}</div>
                            <div class="small text-muted">₱${item.price.toFixed(2)} each</div>
                        </div>
                    </div>
                </div>
            `;
            checkoutItemsContainer.insertAdjacentHTML('beforeend', itemHtml);
        });
        
        // Update total
        const total = getCartTotal();
        checkoutTotalDisplay.textContent = '₱' + total.toFixed(2);
    }
    
    // ========================================
    // CHECKOUT FORM SUBMISSION (COD)
    // ========================================
    
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const cart = getCart();
            
            // Get payment method
            const paymentMethod = document.getElementById('selected_payment_method').value;
            const paypalOrderId = document.getElementById('paypal_order_id').value;
            const paymentStatus = document.getElementById('payment_status').value;
            
            // Validate cart
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            // Get form data
            const formData = new FormData(checkoutForm);
            formData.append('total_amount', getCartTotal());
            formData.append('cart', JSON.stringify(cart));
            formData.append('action', 'placeOrder');
            
            // Show loading
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Processing Order...',
                    html: 'Please wait while we process your order.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            
            // Submit order
            fetch('../../../app/controllers/OrderController.php?action=placeOrder', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear cart from all storage locations
                    saveCart([]);
                    updateCartCount();
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(checkoutModal);
                    if (modal) modal.hide();
                    
                    // Show success message
                    
                    
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Order Failed',
                            text: data.message || 'Failed to place order. Please try again.',
                            confirmButtonColor: '#d33'
                        });
                    } else {
                        alert('Order failed: ' + (data.message || 'Please try again'));
                    }
                }
            })
            .catch(error => {
                console.error('Order error:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                } else {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    }

    // ========================================
    // PAYPAL INTEGRATION - MOVED FROM shop.php
    // ========================================
    
    // Payment method toggle
    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;

            if (this.dataset.payment === 'cod') {
                document.getElementById('selected_payment_method').value = 'Cash on Delivery (COD)';
                document.getElementById('paypal-button-container').style.display = 'none';
                document.getElementById('place-order-btn').style.display = 'block';
            } else if (this.dataset.payment === 'paypal') {
                document.getElementById('selected_payment_method').value = 'PayPal';
                document.getElementById('paypal-button-container').style.display = 'block';
                document.getElementById('place-order-btn').style.display = 'none';
            }
        });
    });

    // Build PayPal Payload Helper
    window.buildPayPalPayload = function() {
        // Priority 1: live CartManager (pagess.js stores to 'shopping_cart')
        let cartData = (window.cartManager && window.cartManager.cart && window.cartManager.cart.length > 0)
            ? window.cartManager.cart
            : (() => { try { return JSON.parse(localStorage.getItem('shopping_cart') || '[]'); } catch(e){ return []; } })();

        // Priority 2: 'cart' key (shop.js legacy localStorage key)
        if (!cartData || cartData.length === 0) {
            try { cartData = JSON.parse(localStorage.getItem('cart') || '[]'); } catch(e) { cartData = []; }
        }

        if (!cartData || cartData.length === 0) return null;

        const items = [];
        let computedTotal = 0;

        cartData.forEach(item => {
            const unitPrice = parseFloat(item.price);
            const qty       = parseInt(item.quantity, 10);
            computedTotal  += unitPrice * qty;

            items.push({
                name:        String(item.name).substring(0, 127),
                description: ('Size: ' + (item.size || 'N/A')).substring(0, 127),
                quantity:    qty,
                price:       unitPrice
            });
        });

        computedTotal = Math.round(computedTotal * 100) / 100;
        return { items, total: computedTotal, cartData };
    };

    // Show PayPal Result Modal
    window.showPayPalResultModal = function(success, message) {
        let modalId = 'paypalResultModal';
        let el = document.getElementById(modalId);
        if (!el) {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                  <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="${modalId}Title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body text-center px-4 pb-4">
                    <div id="${modalId}Icon" class="display-3 mb-3"></div>
                    <p id="${modalId}Msg" class="text-muted"></p>
                  </div>
                  <div class="modal-footer border-0 justify-content-center">
                    <button class="btn btn-dark px-4" data-bs-dismiss="modal">OK</button>
                  </div>
                </div>
              </div>
            </div>`;
            document.body.appendChild(wrapper.firstElementChild);
            el = document.getElementById(modalId);
        }
        el.querySelector(`#${modalId}Title`).textContent = success ? 'Payment Successful!' : 'Payment Failed';
        el.querySelector(`#${modalId}Icon`).innerHTML    = success
            ? '<i class="fas fa-check-circle text-success"></i>'
            : '<i class="fas fa-times-circle text-danger"></i>';
        el.querySelector(`#${modalId}Msg`).textContent   = message;
        new bootstrap.Modal(el).show();
    };

    // ✅ CRITICAL FIX: Store cart data BEFORE PayPal opens
    let storedPayPalData = null;

    // Initialize PayPal Buttons (will be called after PayPal SDK loads)
    window.initPayPalButtons = function() {
        if (typeof paypal === 'undefined') {
            console.error('PayPal SDK not loaded yet');
            return;
        }

        paypal.Buttons({
            createOrder: function (data, actions) {
                const payload = window.buildPayPalPayload();

                if (!payload) {
                    window.showPayPalResultModal(false, 'Your cart is empty. Please add items before paying.');
                    return Promise.reject(new Error('Empty cart'));
                }

                // ✅ CRITICAL FIX: Store cart data BEFORE PayPal popup opens
                const form = document.getElementById('checkoutForm');
                const address = form.querySelector('[name="shipping_address"]').value;
                
                storedPayPalData = {
                    cart: payload.cartData,
                    total_amount: payload.total,
                    shipping_address: address,
                    timestamp: Date.now()
                };
                
                sessionStorage.setItem('paypal_order_data', JSON.stringify(storedPayPalData));
                console.log('✅ PayPal: Stored cart data successfully');

                const orderItems = payload.items.map(item => ({
                    name:        item.name,
                    description: item.description,
                    quantity:    String(item.quantity),
                    unit_amount: {
                        currency_code: 'PHP',
                        value:         item.price.toFixed(2)
                    },
                    category: 'PHYSICAL_GOODS'
                }));

                return actions.order.create({
                    purchase_units: [{
                        description: 'Empire Streetwear Purchase',
                        amount: {
                            currency_code: 'PHP',
                            value: payload.total.toFixed(2),
                            breakdown: {
                                item_total: {
                                    currency_code: 'PHP',
                                    value: payload.total.toFixed(2)
                                }
                            }
                        },
                        items: orderItems
                    }],
                    application_context: {
                        brand_name:  'EMPIRE STREETWEAR',
                        user_action: 'PAY_NOW'
                    }
                });
            },

            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {
                    console.log('✅ PayPal: Payment captured successfully');
                    
                    // ✅ Get stored cart data
                    let orderData = storedPayPalData;
                    
                    if (!orderData) {
                        const stored = sessionStorage.getItem('paypal_order_data');
                        if (stored) {
                            try {
                                orderData = JSON.parse(stored);
                                console.log('✅ PayPal: Retrieved cart data from sessionStorage');
                            } catch (e) {
                                console.error('❌ PayPal: Failed to parse stored data:', e);
                            }
                        }
                    }

                    if (!orderData || !orderData.cart || orderData.cart.length === 0) {
                        alert('Error processing order. Contact support with PayPal ID: ' + data.orderID);
                        console.error('❌ PayPal: No stored cart data found');
                        return;
                    }

                    console.log('✅ PayPal: Processing order with stored data');

                    // Clear cart immediately
                    if (window.cartManager) {
                        window.cartManager.cart = [];
                        window.cartManager.saveCartToStorage();
                        window.cartManager.updateCartDisplay();
                    }
                    localStorage.removeItem('shopping_cart');
                    localStorage.removeItem('cart');

                    // Close checkout modal if open
                    const checkoutModal = bootstrap.Modal.getInstance(
                        document.getElementById('checkoutModal')
                    );
                    if (checkoutModal) checkoutModal.hide();

                    // ⚡ REDIRECT IMMEDIATELY - Don't wait for backend
                    console.log('🚀 Redirecting to payment success page...');
                    window.location.href = '../pages/payment_success.php?token=' + data.orderID + '&paypal_success=1';

                    // Save order in background (this will run but we've already redirected)
                    const formData = new FormData();
                    formData.append('total_amount',     orderData.total_amount.toFixed(2));
                    formData.append('shipping_address', orderData.shipping_address);
                    formData.append('payment_method',   'PayPal');
                    formData.append('cart',             JSON.stringify(orderData.cart));
                    formData.append('paypal_order_id',  data.orderID);
                    formData.append('payment_status',   'paid');
                    formData.append('action',           'placeOrder');

                    // Send to backend but don't wait for response
                    fetch('../../../app/controllers/OrderController.php?action=placeOrder', {
                        method: 'POST',
                        body:   formData,
                        keepalive: true  // Ensures request completes even after redirect
                    }).then(r => r.json()).then(result => {
                        console.log('✅ Order saved:', result);
                        sessionStorage.removeItem('paypal_order_data');
                    }).catch(err => {
                        console.error('❌ Order save error:', err);
                    });
                });
            },

            onCancel: function () {
                sessionStorage.removeItem('paypal_order_data');
                storedPayPalData = null;
                console.log('ℹ️ PayPal: Payment cancelled by user');
                window.showPayPalResultModal(false, 'Payment cancelled. Your cart is still intact.');
            },

            onError: function (err) {
                console.error('❌ PayPal: Error occurred:', err);
                sessionStorage.removeItem('paypal_order_data');
                storedPayPalData = null;
                window.showPayPalResultModal(false, 'A PayPal error occurred. Please try again or choose Cash on Delivery.');
            }

        }).render('#paypal-button-container');
    };
    
    console.log('Shop functionality initialized successfully');
});

// Global function for badge filtering (if needed elsewhere)
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