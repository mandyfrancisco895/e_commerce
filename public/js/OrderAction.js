    // AYUSIN YUNG FILTERING NG SEACRH ASLISIN YNG MONTH YEAR PALITAN NG STATUS FILTER

    // Add this to your JavaScript section
    document.addEventListener('DOMContentLoaded', function() {
        // Order filtering
        const orderSearch = document.getElementById('orderSearch');
        const statusFilter = document.getElementById('orderStatusFilter');
        const dateFilter = document.getElementById('orderDateFilter');
        const orderRows = document.querySelectorAll('.order-row');
    
        function filterOrders() {
            const searchText = orderSearch.value.toLowerCase();
            const statusValue = statusFilter.value;
            const dateValue = dateFilter.value;
    
            orderRows.forEach(row => {
                const customerName = row.getAttribute('data-customer');
                const status = row.getAttribute('data-status');
                const date = row.getAttribute('data-date');
                const orderDate = new Date(date).toISOString().split('T')[0];
    
                const matchesSearch = searchText === '' || customerName.includes(searchText);
                const matchesStatus = statusValue === '' || status === statusValue;
                const matchesDate = dateValue === '' || orderDate === dateValue;
    
                if (matchesSearch && matchesStatus && matchesDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    
        orderSearch.addEventListener('input', filterOrders);
        statusFilter.addEventListener('change', filterOrders);
        dateFilter.addEventListener('change', filterOrders);
    
        document.getElementById('clearOrderFilter').addEventListener('click', function() {
            orderSearch.value = '';
            statusFilter.value = '';
            dateFilter.value = '';
            filterOrders();
        });
    });
    
    function updateOrderStatus(orderId, status) {
        if (confirm('Are you sure you want to update this order status?')) {
            // AJAX call to update order status
            fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating order status');
                }
            });
        }
    }
    
    function printOrder(orderId) {
        window.open(`print_invoice.php?order_id=${orderId}`, '_blank');
    }








    


    // Handle view order button clicks
document.addEventListener('click', function(e) {
    if (e.target.closest('.view-order')) {
        const button = e.target.closest('.view-order');
        const orderId = button.getAttribute('data-orderid');
        
        // Get the order row data
        const orderRow = button.closest('tr');
        
        // Extract data from the row with null checks
        const orderNumber = orderRow.querySelector('td:nth-child(1)')?.textContent || 'N/A';
        const customerCell = orderRow.querySelector('td:nth-child(2)');
        const customerName = customerCell?.querySelector('.fw-medium')?.textContent || 'Unknown Customer';
        const customerEmail = customerCell?.querySelector('.text-muted')?.textContent || 'No email';
        const itemsCount = orderRow.querySelector('td:nth-child(3) .badge')?.textContent || '0 items';
        const totalAmount = orderRow.querySelector('td:nth-child(4)')?.textContent || '₱0.00';
        const status = orderRow.querySelector('td:nth-child(5) .badge')?.textContent || 'Unknown';
        const orderDate = orderRow.querySelector('td:nth-child(6)')?.textContent || 'Unknown date';
        
        // Extract data from button attributes
        const customerPhone = button.getAttribute('data-customer-phone') || 'Not provided';
        const customerAddress = button.getAttribute('data-customer-address') || 'Not provided';
        
        // Populate modal fields with existence checks
        const elements = {
            'view_order_id': orderId,
            'view_order_number': orderNumber,
            'view_customer': customerName,
            'view_email': customerEmail,
            'view_phone': customerPhone,
            'view_address': customerAddress,
            'view_total_amount': totalAmount,
            'view_items_count': itemsCount,
            'view_order_date': orderDate
        };
        
        // Safely populate elements
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                if (id === 'view_items_count') {
                    element.innerHTML = `<span class="badge bg-secondary">${value}</span>`;
                } else {
                    element.textContent = value;
                }
            }
        });
        
        // Handle status badge separately
        const statusElement = document.getElementById('view_order_status');
        if (statusElement) {
            statusElement.innerHTML = `<span class="badge ${getStatusBadgeClass(status.toLowerCase())}">${status}</span>`;
        }
        
        // Load order items
        if (orderId) {
            loadOrderItems(orderId);
        }
        
        // Set up print button
        const printBtn = document.getElementById('printOrderBtn');
        if (printBtn) {
            printBtn.onclick = function() {
                printOrder(orderId);
            };
        }
    }
});

// Handle delete order clicks
document.addEventListener('click', function(e) {
    if (e.target.closest('.delete-order')) {
        const button = e.target.closest('.delete-order');
        const orderId = button.getAttribute('data-order-id');
        
        if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
            deleteOrderAJAX(orderId, button);
        }
    }
});


function deleteOrderAJAX(orderId, button) {
    console.log('Deleting order:', orderId);
    
    // Show loading
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    button.disabled = true;

    // ✅ GET ORDER STATUS BEFORE DELETION
    const orderRow = document.querySelector(`tr[data-id="${orderId}"]`) || 
                   document.querySelector(`[data-order-id="${orderId}"]`) ||
                   button.closest('tr');
    
    let orderStatus = null;
    if (orderRow) {
        const statusBadge = orderRow.querySelector('td:nth-child(5) .badge');
        orderStatus = statusBadge?.textContent?.toLowerCase()?.trim();
        console.log('Order status before deletion:', orderStatus);
    }

    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('action', 'delete_order');

    fetch('admin-dashboard.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Delete response data:', data);
        if (data.success) {
            showNotification('Success', data.message, 'success');
            
            if (orderRow) {
                console.log('Found row to remove:', orderRow);
                orderRow.style.opacity = '0.5';
                orderRow.style.transition = 'opacity 0.5s ease';
                
                setTimeout(() => {
                    orderRow.remove();
                    console.log('Row removed successfully');
                    
                    // ✅ UPDATE STATISTICS AFTER SUCCESSFUL DELETION
                    if (orderStatus) {
                        console.log(`📊 Decreasing ${orderStatus} count by 1`);
                        updateOrderStatistics(orderStatus, null); // Pass null as newStatus
                    }
                }, 500);
            } else {
                console.error('Could not find order row for ID:', orderId);
            }
        } else {
            showNotification('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showNotification('Error', 'An error occurred while deleting order', 'error');
    })
    .finally(() => {
        button.innerHTML = '<i class="bi bi-trash"></i>';
        button.disabled = false;
    });
}


// ✅ NEW FUNCTION: Specifically for order deletion stats
function updateStatsAfterDeletion(deletedStatus) {
    console.log(`📊 Updating stats after deleting ${deletedStatus} order`);
    
    if (!deletedStatus) return;
    
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach(card => {
        const label = card.querySelector('.stat-label');
        const valueElement = card.querySelector('.stat-value');
        
        if (label && valueElement) {
            const labelText = label.textContent.toLowerCase();
            
            // Decrease count for the deleted order's status
            if (labelText.includes(deletedStatus)) {
                updateStatValue(valueElement, -1);
                highlightUpdatedStat(card);
                console.log(`➖ Decreased ${deletedStatus} count`);
            }
        }
    });
}

// Then in your delete function, call:
// updateStatsAfterDeletion(orderStatus);







function updateOrderStatus(orderId, newStatus) {
    if (!orderId || !newStatus) {
        showNotification('Error', 'Invalid order ID or status', 'error');
        return;
    }

    if (!confirm(`Are you sure you want to change the order status to "${newStatus.toUpperCase()}"?`)) {
        return;
    }

    // Store the old status for statistics update - FIXED: Get from data attribute
    const orderRow = document.querySelector(`tr[data-order-id="${orderId}"]`) || 
                     document.querySelector(`button[data-orderid="${orderId}"]`)?.closest('tr');
    
    if (!orderRow) {
        console.error('Order row not found for ID:', orderId);
        showNotification('Error', 'Order not found in table', 'error');
        return;
    }

    // Get old status from badge text or data attribute
    const oldStatusBadge = orderRow.querySelector('td:nth-child(5) .badge');
    const oldStatus = oldStatusBadge?.textContent?.toLowerCase()?.trim() || 
                     orderRow.getAttribute('data-status')?.toLowerCase();

    console.log(`Updating order ${orderId} from "${oldStatus}" to "${newStatus}"`);

    // Show loading state on the specific row
    const statusCell = orderRow.querySelector('td:nth-child(5)');
    if (statusCell) {
        statusCell.innerHTML = `
            <span class="badge bg-secondary">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Updating...
            </span>
        `;
    }

    // Create form data
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', newStatus);
    formData.append('action', 'update_status');

    fetch('admin-dashboard.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response data:', data);
        
        if (data.success) {
            // ✅ CRITICAL FIX: Update ALL instances of the status in the table row
            updateOrderRowStatus(orderRow, data.new_status, orderId);
            
            // ✅ CRITICAL FIX: Update order statistics without page reload
            updateOrderStatistics(oldStatus, data.new_status);
            
            // ✅ Update any open modals with the new status
            updateModalStatus(orderId, data.new_status);

            // ✅ Re-apply current filters to maintain view consistency
            applyCurrentFilters();

            // ✅ Add visual feedback for successful update
            highlightUpdatedRow(orderRow);

            showNotification('Success', data.message, 'success');
            
        } else {
            showNotification('Error', data.message || 'Failed to update order status', 'error');
            // Restore original status on error
            restoreStatusBadge(orderRow, oldStatus);
        }
    })
    .catch(error => {
        console.error('Error updating order status:', error);
        showNotification('Error', 'An error occurred while updating the order status', 'error');
        
        // Restore original status on error
        restoreStatusBadge(orderRow, oldStatus);
    });
}

function updateOrderRowStatus(orderRow, newStatus, orderId) {
    // Update the status badge in the table
    const statusCell = orderRow.querySelector('td:nth-child(5)');
    if (statusCell) {
        const statusClass = getStatusBadgeClass(newStatus);
        const capitalizedStatus = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
        statusCell.innerHTML = `<span class="badge ${statusClass}">${capitalizedStatus}</span>`;
    }

    // Update data attributes
    orderRow.setAttribute('data-status', newStatus);
    if (orderRow.getAttribute('data-order-id')) {
        orderRow.setAttribute('data-order-id', orderId);
    }

    // Update any action buttons that might depend on status
    const actionButtons = orderRow.querySelectorAll('button[data-orderid], .view-order');
    actionButtons.forEach(button => {
        button.setAttribute('data-status', newStatus);
    });

    console.log(`Updated row status to: ${newStatus}`);
}

// ✅ NEW FUNCTION: Update modal status if it's currently open
function updateModalStatus(orderId, newStatus) {
    const modal = document.getElementById('viewOrderModal');
    const modalOrderId = document.getElementById('view_order_id');
    
    // Check if modal is open and shows the same order
    if (modal && modal.classList.contains('show') && 
        modalOrderId && modalOrderId.textContent == orderId) {
        
        const modalStatusElement = document.getElementById('view_order_status');
        if (modalStatusElement) {
            const statusClass = getStatusBadgeClass(newStatus);
            const capitalizedStatus = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            modalStatusElement.innerHTML = `<span class="badge ${statusClass}">${capitalizedStatus}</span>`;
        }
    }
}

// ✅ ENHANCED FUNCTION: Update order statistics in real-time
function updateOrderStatistics(oldStatus, newStatus) {
    console.log(`Updating statistics: ${oldStatus} -> ${newStatus}`);
    
    // Normalize status values
    oldStatus = oldStatus ? oldStatus.toLowerCase().trim() : null;
    newStatus = newStatus ? newStatus.toLowerCase().trim() : null;
    
    // Find stat cards by their label text content (matches your HTML structure)
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach(card => {
        const label = card.querySelector('.stat-label');
        const valueElement = card.querySelector('.stat-value');
        
        if (label && valueElement) {
            const labelText = label.textContent.toLowerCase();
            
            // Decrease count for old status
            if (oldStatus && labelText.includes(oldStatus)) {
                updateStatValue(valueElement, -1);
                highlightUpdatedStat(card);
            }
            
            // Increase count for new status
            if (newStatus && labelText.includes(newStatus)) {
                updateStatValue(valueElement, 1);
                highlightUpdatedStat(card);
            }
        }
    });
}

// ✅ ENHANCED: Update individual stat value with animation
function updateStatValue(element, change) {
    if (!element) return;
    
    const currentValue = parseInt(element.textContent) || 0;
    const newValue = Math.max(0, currentValue + change);
    
    // Add smooth animation
    element.style.transition = 'all 0.3s ease';
    element.style.transform = 'scale(1.05)';
    
    element.textContent = newValue;
    
    setTimeout(() => {
        element.style.transform = 'scale(1)';
    }, 300);
    
    console.log(`Updated stat from ${currentValue} to ${newValue}`);
}

// ✅ ENHANCED: Visual feedback for updated stat cards
function highlightUpdatedStat(card) {
    if (!card) return;
    
    // Add highlight effect
    card.style.transform = 'scale(1.02)';
    card.style.transition = 'all 0.3s ease';
    card.style.boxShadow = '0 4px 15px rgba(0,123,255,0.3)';
    card.style.border = '2px solid rgba(0,123,255,0.4)';
    
    // Remove highlight after 2 seconds
    setTimeout(() => {
        card.style.transform = 'scale(1)';
        card.style.boxShadow = '';
        card.style.border = '';
    }, 2000);
}

// ✅ NEW FUNCTION: Update individual stat value safely
function updateStatValue(element, change) {
    if (!element) return;
    
    const currentValue = parseInt(element.textContent) || 0;
    const newValue = Math.max(0, currentValue + change);
    element.textContent = newValue;
    
    console.log(`Updated stat from ${currentValue} to ${newValue}`);
}

// ✅ ENHANCED FUNCTION: Add visual feedback to updated statistics
function highlightUpdatedStat(status) {
    if (!status) return;
    
    const statusSelectors = {
        'pending': '[data-stat="pending_orders"], .pending-stat',
        'confirmed': '[data-stat="confirmed_orders"], .confirmed-stat',
        'processing': '[data-stat="processing_orders"], .processing-stat', 
        'shipped': '[data-stat="shipped_orders"], .shipped-stat',
        'delivered': '[data-stat="delivered_orders"], .delivered-stat',
        'cancelled': '[data-stat="cancelled_orders"], .cancelled-stat'
    };

    const selector = statusSelectors[status];
    if (selector) {
        const statElements = document.querySelectorAll(selector);
        statElements.forEach(element => {
            const statCard = element.closest('.card, .stat-card, .col') || element;
            if (statCard) {
                // Add highlight effect
                statCard.style.transform = 'scale(1.02)';
                statCard.style.transition = 'all 0.3s ease';
                statCard.style.boxShadow = '0 4px 15px rgba(0,123,255,0.3)';
                statCard.style.zIndex = '10';
                
                // Remove highlight after 2 seconds
                setTimeout(() => {
                    statCard.style.transform = 'scale(1)';
                    statCard.style.boxShadow = '';
                    statCard.style.zIndex = '';
                }, 2000);
            }
        });
    }
}

// ✅ NEW FUNCTION: Highlight the updated row
function highlightUpdatedRow(orderRow) {
    if (!orderRow) return;
    
    orderRow.style.backgroundColor = '#d4edda';
    orderRow.style.transition = 'background-color 0.3s ease';
    
    setTimeout(() => {
        orderRow.style.backgroundColor = '';
    }, 2000);
}

// ✅ ENHANCED FUNCTION: Apply current filters after status update
function applyCurrentFilters() {
    // Small delay to ensure DOM updates are complete
    setTimeout(() => {
        const statusFilter = document.getElementById('orderStatusFilter');
        const searchInput = document.getElementById('orderSearch');
        const dateFilter = document.getElementById('orderDateFilter');

        if (statusFilter && statusFilter.value && statusFilter.value !== 'all') {
            filterOrdersByStatus(statusFilter.value);
        }
        
        if (searchInput && searchInput.value.trim()) {
            filterOrdersBySearch(searchInput.value.trim());
        }
        
        if (dateFilter && dateFilter.value) {
            filterOrdersByDate(dateFilter.value);
        }
    }, 100);
}

// ✅ ENHANCED FUNCTION: Restore status badge on error
function restoreStatusBadge(orderRow, originalStatus) {
    const statusCell = orderRow.querySelector('td:nth-child(5)');
    if (statusCell && originalStatus) {
        const statusClass = getStatusBadgeClass(originalStatus);
        const capitalizedStatus = originalStatus.charAt(0).toUpperCase() + originalStatus.slice(1);
        statusCell.innerHTML = `<span class="badge ${statusClass}">${capitalizedStatus}</span>`;
        console.log(`Restored badge to original status: ${originalStatus}`);
    } else if (statusCell) {
        // If no original status, show generic error
        statusCell.innerHTML = '<span class="badge bg-danger">Update Failed</span>';
    }
}

function getStatusBadgeClass(status) {
    // ✅ CRITICAL FIX: Handle undefined/null status
    if (!status) {
        console.warn('getStatusBadgeClass called with undefined/null status');
        return 'bg-secondary text-white';
    }
    
    // Convert to lowercase for consistency
    const normalizedStatus = String(status).toLowerCase().trim();
    
    const statusClasses = {
        'pending': 'bg-warning text-dark',
        'confirmed': 'bg-info text-white',
        'processing': 'bg-info text-white',
        'shipped': 'bg-primary text-white',
        'delivered': 'bg-success text-white',
        'cancelled': 'bg-danger text-white'
    };
    
    const result = statusClasses[normalizedStatus] || 'bg-secondary text-white';
    console.log(`Status "${status}" (normalized: "${normalizedStatus}") -> class: "${result}"`);
    return result;
}

function showNotification(title, message, type) {
    // Remove existing notifications first
    const existingNotifications = document.querySelectorAll('.notification-alert');
    existingNotifications.forEach(notif => notif.remove());

    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed notification-alert`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 350px;
        max-width: 500px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 8px;
    `;
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi ${iconClass} me-2"></i>
            <div>
                <strong>${title}:</strong> ${message}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification && notification.parentNode) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 150);
        }
    }, 5000);
}

function loadOrderItems(orderId) {
    const itemsContainer = document.getElementById('view_order_items');
    
    if (!itemsContainer) {
        console.error('Order items container not found');
        return;
    }
    
    console.log('Loading items for order ID:', orderId);
    
    // Show loading
    itemsContainer.innerHTML = `
        <tr>
            <td colspan="5" class="text-center py-4">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Loading order items...
            </td>
        </tr>
    `;
    
    // Create form data to call your controller
    const formData = new FormData();
    formData.append('action', 'get_order_items');
    formData.append('order_id', orderId);
    
    fetch('admin-dashboard.php', {  // Call your main dashboard file instead
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data);
        
        if (data.success && data.items && data.items.length > 0) {
            let itemsHtml = '';
            data.items.forEach(item => {
                const productImage = item.product_image || 'placeholder.jpg';
                const productName = item.product_name || 'Unknown Product';
                const size = item.size || 'N/A';
                const quantity = item.quantity || 0;
                // Use product_price from the database query we just updated
                const price = parseFloat(item.product_price) || parseFloat(item.price) || 0;
                const subtotal = parseFloat(item.subtotal) || 0;
                
                itemsHtml += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="/E-COMMERCE/public/uploads/${productImage}" 
                                     alt="${productName}" 
                                     class="rounded me-2" 
                                     style="width: 40px; height: 40px; object-fit: cover;"
                                     onerror="this.src='/E-COMMERCE/public/uploads/placeholder.jpg'">
                                <span>${productName}</span>
                            </div>
                        </td>
                        <td>${size}</td>
                        <td>${quantity}</td>
                        <td>₱${price.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                        <td class="fw-bold">₱${subtotal.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                    </tr>
                `;
            });
            itemsContainer.innerHTML = itemsHtml;
        } else {
            itemsContainer.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <span>No items found</span>
                    </td>
                </tr>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading order items:', error);
        itemsContainer.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                    <span>Error loading items: ${error.message}</span>
                    <br><button class="btn btn-sm btn-outline-danger mt-2" onclick="loadOrderItems(${orderId})">Retry</button>
                </td>
            </tr>
        `;
    });
}

// Improved print function
function printOrder(orderId) {
    const printWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
    
    if (!printWindow) {
        showNotification('Error', 'Please allow pop-ups for this site to print orders.', 'error');
        return;
    }
    
    const modalContent = document.querySelector('#viewOrderModal .modal-content');
    if (!modalContent) {
        showNotification('Error', 'Order details not found. Please try again.', 'error');
        return;
    }
    
    const content = modalContent.innerHTML;
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invoice - Order ${orderId}</title>
            <meta charset="UTF-8">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { 
                    margin: 0; 
                    padding: 20px;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    line-height: 1.5;
                    color: #333;
                }
                .invoice-container { max-width: 800px; margin: 0 auto; padding: 20px; }
                .invoice-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .modal-header, .modal-footer, .btn { display: none !important; }
                .modal-body { padding: 0 !important; }
                table { border-collapse: collapse !important; margin: 20px 0 !important; }
                th, td { padding: 12px 8px !important; border-bottom: 1px solid #ddd !important; }
                th { background-color: #f8f9fa !important; font-weight: bold !important; }
                .invoice-footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
                @media print { body { print-color-adjust: exact; } }
            </style>
        </head>
        <body>
            <div class="invoice-container">
                <div class="invoice-header">
                    <h2>ORDER INVOICE</h2>
                    <p><strong>Order ID:</strong> ${orderId}</p>
                    <p><strong>Print Date:</strong> ${new Date().toLocaleDateString()}</p>
                </div>
                ${content}
                <div class="invoice-footer">
                    <p>Thank you for your business!</p>
                    <p>Generated on ${new Date().toLocaleString()}</p>
                </div>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    
    printWindow.onload = function() {
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    };
}

// ✅ Filter functions with improved selectors
function filterOrdersByStatus(status) {
    const rows = document.querySelectorAll('tbody tr[data-order-id], tbody tr:has(.view-order)');
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status') || 
                         row.querySelector('.badge')?.textContent?.toLowerCase()?.trim();
        
        if (!status || status === 'all' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterOrdersBySearch(searchTerm) {
    const rows = document.querySelectorAll('tbody tr[data-order-id], tbody tr:has(.view-order)');
    const lowerSearchTerm = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const customerName = row.getAttribute('data-customer') || 
                           row.querySelector('td:nth-child(2) .fw-medium')?.textContent || '';
        const orderNumber = row.querySelector('td:first-child')?.textContent || '';
        
        if (customerName.toLowerCase().includes(lowerSearchTerm) || 
            orderNumber.toLowerCase().includes(lowerSearchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterOrdersByDate(date) {
    const rows = document.querySelectorAll('tbody tr[data-order-id], tbody tr:has(.view-order)');
    rows.forEach(row => {
        const rowDate = row.getAttribute('data-date') || 
                       row.querySelector('td:last-child')?.textContent;
        
        if (rowDate && rowDate.startsWith(date)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
