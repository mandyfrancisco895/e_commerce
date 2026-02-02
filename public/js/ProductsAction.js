document.addEventListener('DOMContentLoaded', function() {
    // ===== EDIT PRODUCT MODAL FUNCTIONALITY =====
    const editProductModal = document.getElementById('editProductModal');
    
    if (editProductModal) {
        editProductModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            // Get data from the edit button
            const productId = button.getAttribute('data-id');
            const productName = button.getAttribute('data-name');
            const productDescription = button.getAttribute('data-description');
            const productPrice = button.getAttribute('data-price');
            const productStock = button.getAttribute('data-stock');
            const productCategory = button.getAttribute('data-category');
            const productStatus = button.getAttribute('data-status');
            const productImage = button.getAttribute('data-image');
            const productSizes = button.getAttribute('data-sizes');
            
            // Populate the form fields
            document.getElementById('edit_id').value = productId;
            document.getElementById('edit_name').value = productName;
            document.getElementById('edit_description').value = productDescription;
            document.getElementById('edit_price').value = productPrice;
            document.getElementById('edit_stock').value = productStock;
            
            // Set the category - handle case where category might be inactive
            const categorySelect = document.getElementById('edit_category');
            let categoryFound = false;
            
            for (let i = 0; i < categorySelect.options.length; i++) {
                if (categorySelect.options[i].value === productCategory) {
                    categorySelect.selectedIndex = i;
                    categoryFound = true;
                    break;
                }
            }
            
            // If category not found in active categories, add it temporarily
            if (!categoryFound && productCategory) {
                const newOption = document.createElement('option');
                newOption.value = productCategory;
                newOption.textContent = 'Category #' + productCategory + ' (Inactive)';
                newOption.selected = true;
                categorySelect.appendChild(newOption);
            }
            
            // Handle status dropdown
            const statusSelect = document.getElementById('edit_status');
            const selectedCategoryText = categorySelect.options[categorySelect.selectedIndex].textContent;

            if (parseInt(productStock) === 0) {
                // Rule 1: Stock empty -> Inactive (forced)
                statusSelect.innerHTML = '<option value="inactive" selected>Inactive (No Stock)</option>';
                statusSelect.setAttribute("disabled", "disabled");
            } else if (selectedCategoryText.includes("(Inactive)")) {
                // Rule 2: Category inactive -> Pending (forced)
                statusSelect.innerHTML = '<option value="pending" selected>Pending (Category Inactive)</option>';
                statusSelect.setAttribute("disabled", "disabled");
            } else {
                // Rule 3: Stock > 0 and category active -> Allow choice and show current database status
                statusSelect.innerHTML = `
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="pending">Pending</option>
                `;
                statusSelect.removeAttribute("disabled");

                // Show the ACTUAL database status, not forced "active"
                for (let i = 0; i < statusSelect.options.length; i++) {
                    if (statusSelect.options[i].value === productStatus.toLowerCase()) {
                        statusSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            
            // Handle sizes - uncheck all first, then check the selected ones
            document.querySelectorAll('input[name="edit_sizes[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            if (productSizes && productSizes !== '') {
                const selectedSizes = productSizes.split(',');
                selectedSizes.forEach(size => {
                    const trimmedSize = size.trim();
                    const checkbox = document.getElementById('edit_size_' + trimmedSize);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
            
            document.getElementById('current_image_hidden').value = productImage;
            
            // Show current image if it exists
            const currentImage = document.getElementById('current_image');
            if (productImage && productImage !== '') {
                currentImage.src = '../../../public/uploads/' + productImage;
                currentImage.style.display = 'block';
                currentImage.alt = productName + ' Image';
            } else {
                currentImage.style.display = 'none';
            }
        });
        
        // Add event listener for stock changes to update status automatically
        const editStockInput = document.getElementById('edit_stock');
        if (editStockInput) {
            editStockInput.addEventListener('input', function() {
                const stockValue = parseInt(this.value) || 0;
                const statusSelect = document.getElementById('edit_status');
                const categorySelect = document.getElementById('edit_category');
                const selectedCategoryText = categorySelect.options[categorySelect.selectedIndex].textContent;
                
                if (stockValue === 0) {
                    // Stock is 0 -> Force inactive
                    statusSelect.innerHTML = '<option value="inactive" selected>Inactive (No Stock)</option>';
                    statusSelect.setAttribute("disabled", "disabled");
                } else if (selectedCategoryText.includes("(Inactive)")) {
                    // Stock > 0 but category inactive -> Force pending
                    statusSelect.innerHTML = '<option value="pending" selected>Pending (Category Inactive)</option>';
                    statusSelect.setAttribute("disabled", "disabled");
                } else {
                    // Stock > 0 and category active -> Enable choice, default to active
                    statusSelect.innerHTML = `
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    `;
                    statusSelect.removeAttribute("disabled");
                    statusSelect.value = productStatus.toLowerCase(); // Show current database status
                }
            });
        }
        
        // Clear modal when closed
        editProductModal.addEventListener('hidden.bs.modal', function () {
            const editImageInput = document.getElementById('edit_image');
            if (editImageInput) editImageInput.value = ''; // Clear file input
            
            // Uncheck all size checkboxes
            document.querySelectorAll('input[name="edit_sizes[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Remove any temporarily added inactive category options
            const categorySelect = document.getElementById('edit_category');
            if (categorySelect) {
                const options = categorySelect.options;
                
                for (let i = options.length - 1; i >= 0; i--) {
                    if (options[i].textContent.includes('(Inactive)')) {
                        categorySelect.remove(i);
                    }
                }
            }
            
            // Reset status dropdown to be editable again
            const statusSelect = document.getElementById('edit_status');
            if (statusSelect) {
                statusSelect.innerHTML = `
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="pending">Pending</option>
                `;
                statusSelect.removeAttribute("disabled");
            }
        });
    }

    // ===== VIEW PRODUCT MODAL FUNCTIONALITY =====
    const viewProductModal = document.getElementById('viewProductModal');
    
    if (viewProductModal) {
        viewProductModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            // Get data from the view button
            const productId = button.getAttribute('data-id');
            const productName = button.getAttribute('data-name');
            const productDescription = button.getAttribute('data-description');
            const productPrice = button.getAttribute('data-price');
            const productStock = button.getAttribute('data-stock');
            const productCategory = button.getAttribute('data-category');
            const productStatus = button.getAttribute('data-status');
            const productImage = button.getAttribute('data-image');
            const productSizes = button.getAttribute('data-sizes');
            
            // Populate the modal fields
            document.getElementById('view_id').textContent = productId;
            document.getElementById('view_name').textContent = productName;
            document.getElementById('view_description').textContent = productDescription;
            document.getElementById('view_price').textContent = '₱' + parseFloat(productPrice).toFixed(2);
            document.getElementById('view_stock').textContent = productStock;
            document.getElementById('view_category').textContent = productCategory;
            document.getElementById('view_status').textContent = productStatus.charAt(0).toUpperCase() + productStatus.slice(1);
            
            // Handle sizes display
            const viewSizes = document.getElementById('view_sizes');
            if (viewSizes) {
                if (productSizes && productSizes !== '') {
                    // Convert comma-separated sizes to badges for better display
                    const sizesArray = productSizes.split(',');
                    const sizeBadges = sizesArray.map(size => 
                        `<span class="badge bg-secondary me-1">${size.trim()}</span>`
                    ).join('');
                    viewSizes.innerHTML = sizeBadges;
                } else {
                    viewSizes.innerHTML = '<span class="text-muted">No sizes specified</span>';
                }
            }
            
            // Set product image
            const viewImage = document.getElementById('view_image');
            if (viewImage) {
                if (productImage && productImage !== '') {
                    viewImage.src = '../../../public/uploads/' + productImage;
                    viewImage.alt = productName;
                } else {
                    viewImage.src = '../../../public/uploads/default-product.jpg';
                    viewImage.alt = 'No Image Available';
                }
            }
        });
    }

    // ===== ADD PRODUCT MODAL - RESET FORM =====
    const addProductModal = document.getElementById('addProductModal');
    const addProductForm = document.getElementById('addProductForm');
    
    if (addProductModal && addProductForm) {
        // Reset form when modal is opened
        addProductModal.addEventListener('show.bs.modal', function() {
            addProductForm.reset();
            // Uncheck all size checkboxes
            addProductForm.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        });
        
        // Reset form when modal is closed
        addProductModal.addEventListener('hidden.bs.modal', function() {
            addProductForm.reset();
        });
    }

    // ===== CATEGORY/STATUS HANDLING =====
    function updateStatusBasedOnCategory(categorySelect, statusSelect) {
        if (!categorySelect || !statusSelect) return;
        
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const isInactive = selectedOption.textContent.includes('(Inactive)');
        
        if (isInactive) {
            // Set status to pending and disable the field
            statusSelect.value = 'pending';
            statusSelect.disabled = true;
            statusSelect.title = 'Status is pending because category is inactive';
        } else {
            // Enable the status field for active categories
            statusSelect.disabled = false;
            statusSelect.title = '';
        }
    }
    
    // Add event listener to category dropdown in add modal
    const addCategorySelect = document.querySelector('#addProductModal select[name="category_id"]');
    const addStatusSelect = document.querySelector('#addProductModal select[name="status"]');
    
    if (addCategorySelect && addStatusSelect) {
        addCategorySelect.addEventListener('change', function() {
            updateStatusBasedOnCategory(this, addStatusSelect);
        });
    }
    
    // Add event listener to category dropdown in edit modal
    const editCategorySelect = document.getElementById('edit_category');
    const editStatusSelect = document.getElementById('edit_status');
    
    if (editCategorySelect && editStatusSelect) {
        editCategorySelect.addEventListener('change', function() {
            updateStatusBasedOnCategory(this, editStatusSelect);
        });
        
        // Also check when edit modal is shown
        if (editProductModal) {
            editProductModal.addEventListener('show.bs.modal', function() {
                // Small delay to ensure DOM is updated
                setTimeout(function() {
                    updateStatusBasedOnCategory(editCategorySelect, editStatusSelect);
                }, 100);
            });
        }
    }

    // ===== PRODUCT FILTERING FUNCTIONALITY =====
    const productSearch = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const productsTableBody = document.getElementById('productsTableBody');
    
    if (productSearch && categoryFilter && productsTableBody) {
        const productRows = document.querySelectorAll('.product-row');
        const noResultsRow = document.createElement('tr');
        noResultsRow.innerHTML = '<td colspan="7" class="text-center">No products match your search criteria</td>';
        
        // Create Status Filter dropdown
        const statusFilterContainer = document.createElement('div');
        statusFilterContainer.className = 'd-inline-block';
        statusFilterContainer.innerHTML = `
            <select class="form-select d-inline-block w-auto me-2" id="statusFilter">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="pending">Pending</option>
            </select>
        `;
        
        // Create Clear Filters button
        const clearFiltersButton = document.createElement('button');
        clearFiltersButton.className = 'btn btn-outline-secondary';
        clearFiltersButton.innerHTML = '<i class="bi bi-x-circle me-1"></i> Clear';
        clearFiltersButton.type = 'button';
        
        // Add status filter and clear button to the table header (right side with category filter)
        const filterContainer = categoryFilter.parentElement;
        if (filterContainer) {
            // Insert status filter before category filter
            filterContainer.insertBefore(statusFilterContainer, categoryFilter);
            // Add clear button after category filter
            filterContainer.appendChild(clearFiltersButton);
        }
        
        const statusFilter = document.getElementById('statusFilter');
        
        // Function to filter products
        function filterProducts() {
            const searchText = productSearch.value.toLowerCase();
            const categoryValue = categoryFilter.value;
            const statusValue = statusFilter ? statusFilter.value : '';
            
            let visibleCount = 0;
            
            productRows.forEach(row => {
                const productName = row.getAttribute('data-name');
                const productCategory = row.getAttribute('data-category');
                const productStatus = row.getAttribute('data-status');
                
                // Get additional data from table cells for searching
                const productId = row.cells[1].textContent.toLowerCase(); // ID column
                const productPrice = row.cells[3].textContent.toLowerCase(); // Price column
                const productCategoryName = row.cells[2].textContent.toLowerCase(); // Category name column
                
                // Check if matches search text in multiple fields
                const matchesSearch = searchText === '' || 
                    productName.includes(searchText) ||
                    productId.includes(searchText) ||
                    productPrice.includes(searchText) ||
                    productCategoryName.includes(searchText);
                
                const matchesCategory = categoryValue === '' || productCategory === categoryValue;
                const matchesStatus = statusValue === '' || productStatus === statusValue;
                
                if (matchesSearch && matchesCategory && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            const existingNoResults = document.querySelector('.no-results-message');
            if (existingNoResults) {
                existingNoResults.remove();
            }
            
            if (visibleCount === 0) {
                productsTableBody.appendChild(noResultsRow);
                noResultsRow.classList.add('no-results-message');
            }
        }
        
        // Add event listeners
        productSearch.addEventListener('input', filterProducts);
        categoryFilter.addEventListener('change', filterProducts);
        if (statusFilter) {
            statusFilter.addEventListener('change', filterProducts);
        }
        
        // Clear filters functionality
        clearFiltersButton.addEventListener('click', function() {
            productSearch.value = '';
            categoryFilter.value = '';
            if (statusFilter) {
                statusFilter.value = '';
            }
            filterProducts();
        });
        
        // Update search placeholder
        productSearch.placeholder = "Search by name, ID, price, or category...";
        
        // Initialize filter on page load
        filterProducts();
    }
});