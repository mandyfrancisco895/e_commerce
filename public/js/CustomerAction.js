document.addEventListener('DOMContentLoaded', function() {
    // ===== VIEW CUSTOMER MODAL FUNCTIONALITY =====
    const viewCustomerModal = document.getElementById('viewCustomerModal');
    
    if (viewCustomerModal) {
        // Clear any existing event listeners and start fresh
        viewCustomerModal.addEventListener('show.bs.modal', function(event) {
            console.log('View customer modal opening...');
            
            const button = event.relatedTarget;
            if (!button) {
                console.error('No button found that triggered modal');
                return;
            }

            // Clear all fields first
            clearModalFields();

            // Get all the data from button attributes
            const modalData = {
                customerId: button.getAttribute('data-id'),
                customerName: button.getAttribute('data-name'),
                customerEmail: button.getAttribute('data-email'),
                customerPhone: button.getAttribute('data-phone'),
                customerOrders: button.getAttribute('data-orders'),
                customerSpent: button.getAttribute('data-spent'),
                customerStatus: button.getAttribute('data-status'),
                customerJoined: button.getAttribute('data-joined'),
                customerAddress: button.getAttribute('data-address'),
                customerProfilePic: button.getAttribute('data-profile-pic')
            };

            // Debug: log all the data we extracted
            console.log('Extracted modal data:', modalData);

            // Populate the modal with customer data
            populateCustomerDetails(modalData);

            // Extract numeric customer ID for orders
            const customerIdNum = modalData.customerId ? modalData.customerId.replace('U', '') : null;
            if (customerIdNum) {
                loadCustomerOrders(customerIdNum);
            }
        });
    }

    function clearModalFields() {
        const fields = [
            'view_customer_id',
            'view_customer_name', 
            'view_customer_email',
            'view_customer_phone',
            'view_customer_orders',
            'view_customer_spent',
            'view_customer_status',
            'view_customer_joined',
            'view_customer_address',
            'view_customer_avatar'
        ];

        fields.forEach(fieldId => {
            const element = document.getElementById(fieldId);
            if (element) {
                if (fieldId === 'view_customer_status') {
                    element.textContent = '-';
                    element.className = 'badge bg-secondary';
                } else {
                    element.textContent = fieldId === 'view_customer_avatar' ? '--' : '-';
                }
            }
        });
    }

    function populateCustomerDetails(data) {
        console.log('Populating customer details...');

        // Customer ID
        const idElement = document.getElementById('view_customer_id');
        if (idElement && data.customerId) {
            idElement.textContent = data.customerId;
            console.log('Set customer ID:', data.customerId);
        }

        // Customer Name
        const nameElement = document.getElementById('view_customer_name');
        if (nameElement && data.customerName) {
            nameElement.textContent = data.customerName;
            console.log('Set customer name:', data.customerName);
        }

        // Email
        const emailElement = document.getElementById('view_customer_email');
        if (emailElement && data.customerEmail) {
            emailElement.textContent = data.customerEmail;
        }

        // Phone
        const phoneElement = document.getElementById('view_customer_phone');
        if (phoneElement) {
            phoneElement.textContent = data.customerPhone || 'N/A';
        }

        // Orders count
        const ordersElement = document.getElementById('view_customer_orders');
        if (ordersElement && data.customerOrders) {
            ordersElement.textContent = data.customerOrders + ' orders';
        }

        // Total spent
        const spentElement = document.getElementById('view_customer_spent');
        if (spentElement && data.customerSpent) {
            const amount = parseFloat(data.customerSpent) || 0;
            spentElement.textContent = '₱' + amount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Status
        const statusElement = document.getElementById('view_customer_status');
        if (statusElement && data.customerStatus) {
            statusElement.textContent = data.customerStatus;
            const statusClass = data.customerStatus === 'Active' ? 'bg-success' : 'bg-secondary';
            statusElement.className = 'badge ' + statusClass;
        }

        // Joined date
        const joinedElement = document.getElementById('view_customer_joined');
        if (joinedElement) {
            joinedElement.textContent = data.customerJoined || 'N/A';
        }

        // Address
        const addressElement = document.getElementById('view_customer_address');
        if (addressElement) {
            addressElement.textContent = data.customerAddress || 'N/A';
        }

        // Avatar/Profile Picture
        const avatarElement = document.getElementById('view_customer_avatar');
        if (avatarElement) {
            if (data.customerProfilePic && data.customerProfilePic.trim() !== '') {
                // Show profile picture
                avatarElement.innerHTML = `
                    <img src="/E-COMMERCE/public/uploads/${data.customerProfilePic}" 
                         alt="${data.customerName || 'Customer'}" 
                         class="rounded-circle"
                         style="width: 80px; height: 80px; object-fit: cover;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="bg-primary text-white rounded-circle align-items-center justify-content-center" 
                         style="width: 80px; height: 80px; font-size: 2rem; display: none;">
                        ${getInitials(data.customerName)}
                    </div>
                `;
            } else {
                // Show initials only
                const initials = getInitials(data.customerName);
                avatarElement.innerHTML = `
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px; font-size: 2rem;">
                        ${initials}
                    </div>
                `;
            }
        }

        console.log('Customer details populated successfully');
    }

    // Helper function to get initials
    function getInitials(name) {
        if (!name) return '--';
        const names = name.trim().split(' ');
        return names.map(n => n.charAt(0).toUpperCase()).join('').substring(0, 2);
    }

    function loadCustomerOrders(customerId) {
        const ordersTableBody = document.getElementById('customer_orders_table');
        if (!ordersTableBody) {
            console.error('Orders table body not found');
            return;
        }

        // Show loading state
        ordersTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading orders...</span>
                </td>
            </tr>
        `;

        // Fetch orders
        const controllerPath = '../../controllers/OrderController.php';
        const url = `${controllerPath}?action=getCustomerOrders&customer_id=${customerId}`;
        
        console.log('Fetching orders from:', url);

        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed data:', data);
                    
                    if (data.success) {
                        displayOrders(data.orders);
                    } else {
                        showOrdersError(data.message || 'Failed to load orders');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e);
                    showOrdersError('Invalid response from server');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showOrdersError('Network error occurred');
            });
    }

    function displayOrders(orders) {
        const ordersTableBody = document.getElementById('customer_orders_table');
        if (!ordersTableBody) return;

        if (!orders || orders.length === 0) {
            ordersTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-basket" style="font-size: 1.5rem;"></i>
                        <p class="mt-2 mb-0">No orders found</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        orders.forEach(order => {
            html += `
                <tr>
                    <td class="fw-bold text-primary">${order.order_id || 'N/A'}</td>
                    <td>${order.date || 'N/A'}</td>
                    <td>${order.items || '0 items'}</td>
                    <td class="fw-bold">${order.total || '₱0.00'}</td>
                    <td><span class="badge ${order.status_class || 'bg-secondary'}">${order.status || 'Unknown'}</span></td>
                </tr>
            `;
        });
        
        ordersTableBody.innerHTML = html;
        console.log('Orders displayed successfully');
    }

    function showOrdersError(message) {
        const ordersTableBody = document.getElementById('customer_orders_table');
        if (!ordersTableBody) return;
        
        ordersTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 1.5rem;"></i>
                    <p class="mt-2 mb-0">${message}</p>
                </td>
            </tr>
        `;
    }

    // ===== EDIT CUSTOMER MODAL FUNCTIONALITY =====
    const editCustomerModal = document.getElementById('editCustomerModal');
    let formPopulated = false;
    
    if (editCustomerModal) {
        editCustomerModal.addEventListener('show.bs.modal', function(event) {
            console.log('Edit customer modal opening...');
            formPopulated = false;
            
            const button = event.relatedTarget;
            if (!button) {
                return;
            }

            const userId = button.getAttribute('data-userid') || button.dataset.userid;
            const username = button.getAttribute('data-username') || button.dataset.username;
            const email = button.getAttribute('data-email') || button.dataset.email;
            const phone = button.getAttribute('data-phone') || button.dataset.phone || '';
            const address = button.getAttribute('data-address') || button.dataset.address || '';
            const status = button.getAttribute('data-status') || button.dataset.status;

            console.log('📝 Edit modal data received:', {
                userId, username, email, phone, address, status
            });

            if (!userId || !username || !email) {
                alert('Error: Customer data is incomplete. Please refresh the page and try again.');
                return;
            }

            populateEditForm({
                userId, username, email, phone, address, status
            });
        });

        editCustomerModal.addEventListener('hidden.bs.modal', function() {
            if (formPopulated) {
                const form = document.getElementById('editCustomerForm');
                if (form) {
                    form.reset();
                    formPopulated = false;
                }
            }
        });
    }

    function populateEditForm(data) {
        setTimeout(() => {
            const customerIdField = document.getElementById('edit_customer_id');
            if (customerIdField) {
                customerIdField.value = data.userId;
            } else {
                alert('CRITICAL ERROR: Customer ID field not found. Cannot proceed with edit.');
                return;
            }

            const names = data.username ? data.username.trim().split(' ') : ['', ''];
            const firstName = names[0] || '';
            const lastName = names.slice(1).join(' ') || '';

            const firstNameField = document.getElementById('edit_customer_first_name');
            if (firstNameField) {
                firstNameField.value = firstName;
            }

            const lastNameField = document.getElementById('edit_customer_last_name');
            if (lastNameField) {
                lastNameField.value = lastName;
            }

            const emailField = document.getElementById('edit_customer_email');
            if (emailField) {
                emailField.value = data.email;
            }

            const phoneField = document.getElementById('edit_customer_phone');
            if (phoneField) {
                phoneField.value = data.phone || '';
            }

            const addressField = document.getElementById('edit_customer_address');
            if (addressField) {
                addressField.value = data.address || '';
            }

            // Display status in read-only field
            const statusDisplayField = document.getElementById('edit_customer_status_display');
            if (statusDisplayField && data.status) {
                let statusValue = data.status.toString().trim();
                
                console.log('🔍 Original status value:', statusValue);
                
                // Clean and display the status
                if (statusValue === 'Active' || statusValue === 'active') {
                    statusDisplayField.value = 'Active';
                } else if (statusValue === 'Deactivated' || statusValue === 'deactivated') {
                    statusDisplayField.value = 'Deactivated';
                } else if (statusValue === 'Inactive' || statusValue === 'inactive') {
                    statusDisplayField.value = 'Deactivated';
                } else if (statusValue === 'Blocked' || statusValue === 'blocked' || statusValue === 'Block') {
                    statusDisplayField.value = 'Blocked';
                } else {
                    console.warn('⚠️ Unknown status value, showing as-is:', statusValue);
                    statusDisplayField.value = statusValue;
                }
                
                console.log('✅ Status displayed as:', statusDisplayField.value);
                updateStatusDescription(statusDisplayField.value);
            }

            formPopulated = true;
            
            const finalCustomerId = document.getElementById('edit_customer_id').value;
            if (!finalCustomerId) {
                alert('CRITICAL ERROR: Customer ID could not be set. Please refresh the page and try again.');
            }
        }, 100);
    }

    function updateStatusDescription(status) {
        const statusDescription = document.getElementById('statusDescription');
        if (!statusDescription) return;

        const descriptions = {
            'Active': 'Customer can place orders and access their account.',
            'Deactivated': 'Customer cannot place orders but can still login.',
            'Blocked': 'Customer cannot access their account or place orders.'
        };

        statusDescription.textContent = descriptions[status] || 'Current customer status (read-only).';
    }

    const editForm = document.getElementById('editCustomerForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const customerId = document.getElementById('edit_customer_id').value;
            const firstName = document.getElementById('edit_customer_first_name').value.trim();
            const lastName = document.getElementById('edit_customer_last_name').value.trim();
            const email = document.getElementById('edit_customer_email').value.trim();

            console.log('📤 Form submission data:', {
                customerId, firstName, lastName, email
            });

            if (!customerId) {
                e.preventDefault();
                alert('CRITICAL ERROR: Customer ID is missing! Please close the modal and try again.');
                return false;
            }

            if (!firstName || !lastName || !email) {
                e.preventDefault();
                alert('Please fill in all required fields (First Name, Last Name, and Email)');
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }

            console.log('✅ Form validation passed, submitting...');
        });
    }

    // ===== SEARCH AND FILTER FUNCTIONALITY =====
    // Customer search functionality
    const customerSearch = document.getElementById('customerSearch');
    if (customerSearch) {
        customerSearch.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#customersTableBody .customer-row');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                
                if (name.toLowerCase().includes(searchText) || email.toLowerCase().includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Status filter functionality
    const customerStatusFilter = document.getElementById('customerStatusFilter');
    if (customerStatusFilter) {
        customerStatusFilter.addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('#customersTableBody .customer-row');
            
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                
                if (!status || rowStatus === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
