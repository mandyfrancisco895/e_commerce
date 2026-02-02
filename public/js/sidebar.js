

    document.addEventListener('DOMContentLoaded', function() {
    // Handle navigation link clicks
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    const contentSections = document.querySelectorAll('.content-section');
    
    // Function to show a specific section
    function showSection(sectionId) {
        console.log('Showing section:', sectionId); // Debug log
        
        // Hide all sections
        contentSections.forEach(section => {
            section.classList.add('d-none');
        });
        
        // Show the target section
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.remove('d-none');
            console.log('Section found and shown'); // Debug log
        } else {
            console.log('Section not found:', sectionId); // Debug log
        }
        
        // Update active state in sidebar
        navLinks.forEach(navLink => {
            navLink.classList.remove('active');
            if (navLink.getAttribute('data-bs-target') === sectionId) {
                navLink.classList.add('active');
            }
        });
        
        // Update URL hash (remove -section for cleaner URLs)
        window.location.hash = sectionId.replace('-section', '');
        
        // Update page title
        const activeLink = document.querySelector(`[data-bs-target="${sectionId}"]`);
        if (activeLink) {
            const pageTitle = document.getElementById('pageTitle');
            if (pageTitle) {
                pageTitle.textContent = activeLink.querySelector('.nav-text').textContent;
            }
        }
    }
    
    // Set up click handlers for all nav links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-bs-target');
            console.log('Link clicked, target:', targetId); // Debug log
            showSection(targetId);
        });
    });
    
    // Handle URL hash on page load
    function handleHashOnLoad() {
        const hash = window.location.hash;
        console.log('URL hash on load:', hash); // Debug log
        
        if (hash) {
            // Add -section to the hash to match section IDs
            const targetSection = hash.replace('#', '') + '-section';
            if (document.getElementById(targetSection)) {
                showSection(targetSection);
                return;
            }
        }
        // Default to dashboard if no valid hash
        showSection('dashboard-section');
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('hashchange', function() {
        const hash = window.location.hash;
        console.log('Hash changed:', hash); // Debug log
        
        if (hash) {
            const targetSection = hash.replace('#', '') + '-section';
            if (document.getElementById(targetSection)) {
                showSection(targetSection);
            }
        }
    });
    
    // Initial load
    handleHashOnLoad();

    
    // Handle sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });
    }

    // AJAX form submission for update (from previous solution)
    const updateForm = document.getElementById('updateProductForm');
    
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = updateForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editProductModal'));
                    modal.hide();
                    
                    // Ensure we stay on products section
                    showSection('products-section');
                    
                    // Optional: Refresh the page after 1.5 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                    
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Network error. Please try again.', 'danger');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Function to show alerts
    function showAlert(message, type) {
        const existingAlerts = document.querySelectorAll('.alert-dismissible');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
