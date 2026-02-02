
// Profile Modal Functions

// Profile Modal Functions
function openProfileModal() {
    const profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
    profileModal.show();
}

function openEditProfile() {
    // Hide profile modal and show edit modal
    bootstrap.Modal.getInstance(document.getElementById('profileModal')).hide();
    const editModal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    editModal.show();
}

function openChangePassword() {
    // Hide edit profile modal and show change password modal
    bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
    const changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    changePasswordModal.show();
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

// UPDATED: Image preview functionality with correct paths
function previewImage(input) {
    const preview = document.getElementById('profileImagePreview');
    const placeholder = document.getElementById('profileImagePlaceholder');
    const file = input.files[0];
    
    if (file) {
        // Check file size (max 2MB)
        const fileSize = file.size / 1024 / 1024;
        if (fileSize > 2) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }
        
        // Check file type
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPEG, PNG, GIF, WEBP)');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Hide placeholder and show preview
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            // Hide remove checkbox since we're uploading a new image
            const removeCheckbox = document.getElementById('removeProfilePic');
            if (removeCheckbox) {
                removeCheckbox.checked = false;
                const removeOption = removeCheckbox.closest('.remove-image-option');
                if (removeOption) removeOption.style.display = 'none';
            }
        }
        
        reader.readAsDataURL(file);
    }
}

// Remove image selection
function removeImage() {
    const fileInput = document.getElementById('profileImage');
    const preview = document.getElementById('profileImagePreview');
    const placeholder = document.getElementById('profileImagePlaceholder');
    
    if (fileInput) fileInput.value = '';
    
    if (preview && placeholder) {
        preview.style.display = 'none';
        placeholder.style.display = 'flex';
    }
    
    // Check the remove profile pic checkbox
    const removeCheckbox = document.getElementById('removeProfilePic');
    if (removeCheckbox) removeCheckbox.checked = true;
}

// UPDATED: Function to update top bar avatar with correct E-COMMERCE paths
function updateTopBarAvatar(profilePic, username) {
    console.log('Updating avatar:', profilePic, username);
    
    // Find all avatar elements in dropdown
    const avatarImages = document.querySelectorAll('.avatar img');
    const avatarPlaceholders = document.querySelectorAll('.avatar span');
    
    if (profilePic) {
        // Ensure the path uses the correct E-COMMERCE format
        let imagePath = profilePic;
        if (!imagePath.includes('/E-COMMERCE/public/uploads/')) {
            imagePath = '/E-COMMERCE/public/uploads/' + profilePic.replace(/^.*[\\\/]/, ''); // Get filename only
        }
        
        console.log('Setting image path:', imagePath);
        
        // Update all avatar images
        avatarImages.forEach(img => {
            img.src = imagePath + '?v=' + new Date().getTime();
            img.style.display = 'block';
            img.onerror = function() {
                this.style.display = 'none';
                // Show the next sibling (placeholder span)
                if (this.nextElementSibling) {
                    this.nextElementSibling.style.display = 'flex';
                }
            };
        });
        
        // Hide placeholders
        avatarPlaceholders.forEach(span => {
            span.style.display = 'none';
        });
        
    } else {
        // Hide images and show placeholders
        avatarImages.forEach(img => {
            img.style.display = 'none';
        });
        
        avatarPlaceholders.forEach(span => {
            span.style.display = 'flex';
            span.textContent = username ? username.charAt(0).toUpperCase() : 'U';
        });
    }
}

// UPDATED: Function to update all profile displays
function updateAllProfileDisplays(data) {
    console.log('Updating all profile displays:', data);
    
    // Update dropdown avatar
    updateTopBarAvatar(data.profile_pic, data.username);
    
    // Update profile modal if open
    const profileModal = document.getElementById('profileModal');
    if (profileModal && profileModal.classList.contains('show')) {
        updateProfileModal(data);
    }
    
    // Update edit modal current display
    updateEditModalDisplay(data);
}

// UPDATED: Update profile modal with new data
function updateProfileModal(data) {
    const modal = document.getElementById('profileModal');
    if (!modal) return;
    
    // Update profile image
    const profileImg = modal.querySelector('.profile-image');
    const profilePlaceholder = modal.querySelector('.profile-image-placeholder');
    
    if (data.profile_pic) {
        let imagePath = data.profile_pic;
        if (!imagePath.includes('/E-COMMERCE/public/uploads/')) {
            imagePath = '/E-COMMERCE/public/uploads/' + imagePath.replace(/^.*[\\\/]/, '');
        }
        
        if (profileImg) {
            profileImg.src = imagePath + '?v=' + new Date().getTime();
            profileImg.style.display = 'block';
        }
        if (profilePlaceholder) {
            profilePlaceholder.style.display = 'none';
        }
    } else {
        if (profileImg) profileImg.style.display = 'none';
        if (profilePlaceholder) {
            profilePlaceholder.style.display = 'flex';
            profilePlaceholder.textContent = data.username ? data.username.charAt(0).toUpperCase() : 'U';
        }
    }
    
    // Update text fields
    const usernameElements = modal.querySelectorAll('h5');
    usernameElements.forEach(el => {
        if (el.textContent.trim() !== 'My Profile') {
            el.textContent = data.username || '';
        }
    });
    
    // Update other profile information
    const infoRows = modal.querySelectorAll('.row');
    infoRows.forEach(row => {
        const label = row.querySelector('label');
        if (label) {
            const field = label.textContent.toLowerCase();
            const valueElement = row.querySelector('p');
            
            if (field.includes('email') && valueElement) {
                valueElement.textContent = data.email || '';
            } else if (field.includes('phone') && valueElement) {
                valueElement.innerHTML = '<i class="bi bi-telephone-fill text-primary me-1"></i>' + 
                    (data.phone || '<span class="text-muted fst-italic">Not provided</span>');
            } else if (field.includes('address') && valueElement) {
                valueElement.innerHTML = '<i class="bi bi-geo-alt-fill text-primary me-1"></i>' + 
                    (data.address || '<span class="text-muted fst-italic">Not provided</span>');
            }
        }
    });
}

// UPDATED: Update edit modal display
function updateEditModalDisplay(data) {
    const modal = document.getElementById('editProfileModal');
    if (!modal) return;
    
    // Update the preview image
    const preview = modal.querySelector('#profileImagePreview');
    const placeholder = modal.querySelector('#profileImagePlaceholder');
    
    if (data.profile_pic) {
        let imagePath = data.profile_pic;
        if (!imagePath.includes('/E-COMMERCE/public/uploads/')) {
            imagePath = '/E-COMMERCE/public/uploads/' + imagePath.replace(/^.*[\\\/]/, '');
        }
        
        if (preview) {
            preview.src = imagePath + '?v=' + new Date().getTime();
            preview.style.display = 'block';
            // Store as original for reset functionality
            preview.dataset.original = preview.src;
        }
        if (placeholder) {
            placeholder.style.display = 'none';
        }
    } else {
        if (preview) preview.style.display = 'none';
        if (placeholder) {
            placeholder.style.display = 'flex';
            placeholder.textContent = data.username ? data.username.charAt(0).toUpperCase() : 'U';
        }
    }
}

// Password validation
document.addEventListener('DOMContentLoaded', function() {
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    }
});

// UPDATED: Edit profile form submission
document.addEventListener('DOMContentLoaded', function() {
    const editProfileForm = document.getElementById('editProfileForm');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
            const username = document.getElementById('editUsername').value;
            const email = document.getElementById('editEmail').value;
            
            // Basic validation
            if (!username.trim()) {
                e.preventDefault();
                alert('Username is required!');
                return false;
            }
            
            if (!email.trim()) {
                e.preventDefault();
                alert('Email is required!');
                return false;
            }
            
            // Email format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address!');
                return false;
            }
            
            // Check file size if an image is selected
            const fileInput = document.getElementById('profileImage');
            if (fileInput && fileInput.files[0]) {
                const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
                if (fileSize > 2) {
                    e.preventDefault();
                    alert('File size must be less than 2MB');
                    return false;
                }
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                submitBtn.disabled = true;
                
                // Restore button after form submission
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 3000);
            }
            
            // Allow form to submit normally (no AJAX to keep it simple)
            return true;
        });
    }
});

// UPDATED: Handle remove profile picture checkbox
document.addEventListener('DOMContentLoaded', function() {
    const removeCheckbox = document.getElementById('removeProfilePic');
    if (removeCheckbox) {
        removeCheckbox.addEventListener('change', function() {
            const preview = document.getElementById('profileImagePreview');
            const placeholder = document.getElementById('profileImagePlaceholder');
            const fileInput = document.getElementById('profileImage');
            
            if (this.checked) {
                // Show placeholder, hide preview
                if (preview) preview.style.display = 'none';
                if (placeholder) placeholder.style.display = 'flex';
                if (fileInput) fileInput.value = ''; // Clear file input
            } else {
                // Restore original image if available
                if (preview && preview.dataset.original) {
                    preview.src = preview.dataset.original;
                    preview.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                }
            }
        });
    }
});

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        if (document.body.contains(toast)) {
            document.body.removeChild(toast);
        }
    });
}

// UPDATED: Initialize modals with enhanced functionality
document.addEventListener('DOMContentLoaded', function() {
    // Store original image source for cancel operations
    const profilePreview = document.getElementById('profileImagePreview');
    if (profilePreview && profilePreview.src && profilePreview.src !== window.location.href) {
        profilePreview.dataset.original = profilePreview.src;
    }
    
    // Reset edit modal when closed
    const editProfileModal = document.getElementById('editProfileModal');
    if (editProfileModal) {
        editProfileModal.addEventListener('hidden.bs.modal', function() {
            const profilePreview = document.getElementById('profileImagePreview');
            const placeholder = document.getElementById('profileImagePlaceholder');
            const fileInput = document.getElementById('profileImage');
            const removeCheckbox = document.getElementById('removeProfilePic');
            
            // Reset image preview
            if (profilePreview && profilePreview.dataset.original) {
                profilePreview.src = profilePreview.dataset.original;
                if (profilePreview.dataset.original !== window.location.href) {
                    profilePreview.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                }
            }
            
            // Reset file input
            if (fileInput) fileInput.value = '';
            
            // Reset remove checkbox
            if (removeCheckbox) {
                removeCheckbox.checked = false;
                const removeOption = removeCheckbox.closest('.remove-image-option');
                if (removeOption) removeOption.style.display = 'block';
            }
        });
    }
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) modalInstance.hide();
            });
        }
    });
});
